<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Entity\AllocationDraft;
use Marello\Bundle\InventoryBundle\Entity\AllocationDraftItem;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumDistance\MinimumDistanceWFAStrategy;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class InventoryAllocationProvider
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /** @var OrderWarehousesProviderInterface $warehousesProvider */
    protected $warehousesProvider;

    /**
     * @var WFAStrategiesRegistry
     */
    protected $strategiesRegistry;

    /**
     * @var RuleFiltrationServiceInterface
     */
    protected $rulesFiltrationService;

    /**
     * @var WFARuleRepository
     */
    protected $wfaRuleRepository;

    /**
     * InventoryAllocationProvider constructor.
     * @param DoctrineHelper $doctrineHelper
     * @param OrderWarehousesProviderInterface $warehousesProvider
     * @param WFAStrategiesRegistry $strategiesRegistry
     * @param RuleFiltrationServiceInterface $rulesFiltrationService
     * @param WFARuleRepository $wfaRuleRepository
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        OrderWarehousesProviderInterface $warehousesProvider,
        WFAStrategiesRegistry $strategiesRegistry,
        RuleFiltrationServiceInterface $rulesFiltrationService,
        WFARuleRepository $wfaRuleRepository
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->warehousesProvider = $warehousesProvider;
        $this->strategiesRegistry = $strategiesRegistry;
        $this->rulesFiltrationService = $rulesFiltrationService;
        $this->wfaRuleRepository = $wfaRuleRepository;
    }

    /**
     * @param Order $order
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function allocateOrderToWarehouses(Order $order)
    {
        // check if order needs to be consolidated
        $consolidation = $order->isConsolidationEnabled();
        $consolidationWarehouse = null;
        if ($consolidation) {
            $consolidationWarehouse = $this->getConsolidationWarehouse($order);
        }

        // consolidation is not enabled, so we just run the 'normal' WFA rules (all of them)
        // the result of the WFA rules is also the input for the AllocationDraft/AllocationDraftItems
        // create all allocationDrafts/draft items
        $allOrderItems = new ArrayCollection();
        $allItems = [];
        $subAllocations = [];
        $em = $this->doctrineHelper->getEntityManagerForClass(AllocationDraft::class);
        foreach ($this->warehousesProvider->getWarehousesForOrder($order) as $orderWarehouseResults) {
            foreach ($orderWarehouseResults as $result) {
                /** @var Order $order */
                $allocationDraft = new AllocationDraft();
                $allocationDraft->setOrder($order);
                $allocationDraft->setType('On Hand');
                if ($result->getWarehouse()->getCode() === 'no_warehouse') {
                    $allocationDraft->setType('Waiting for supply');
                }
                if ($result->getWarehouse()->getCode() === 'could_not_allocate') {
                    $allocationDraft->setType('Could not Allocate');
                }
                if (!in_array($result->getWarehouse()->getCode(), ['no_warehouse', 'could_not_allocate'])) {
                    $allocationDraft->setWarehouse($result->getWarehouse());
                }
                $shippingAddress = $order->getShippingAddress();
                if ($consolidationWarehouse) {
                    if ($consolidationWarehouse->getCode() !== $result->getWarehouse()->getCode()) {
                        $shippingAddress = $consolidationWarehouse->getAddress();
                    }
                }

                $allocationDraft->setShippingAddress($shippingAddress);
                $itemWithQty = $result->getItemsWithQuantity();
                foreach ($result->getOrderItems() as $item) {
                    $allocationDraftItem = new AllocationDraftItem();
                    $allocationDraftItem->setOrderItem($item);
                    $allocationDraftItem->setProduct($item->getProduct());
                    $allocationDraftItem->setProductSku($item->getProductSku());
                    $allocationDraftItem->setProductName($item->getProductName());
                    if ($allocationDraft->getWarehouse()) {
                        $allocationDraftItem->setWarehouse($allocationDraft->getWarehouse());
                    }
                    $allocationDraftItem->setQuantity($itemWithQty[$item->getProductSku()]);
                    $allocationDraft->addItem($allocationDraftItem);
                    $allOrderItems->add($item);
                    if ($consolidationWarehouse) {
                        $allItems[] = clone $allocationDraftItem;
                        $subAllocations[] = $allocationDraft;
                    }
                }
                $em->persist($allocationDraft);
            }
        }
        $diff = [];
        foreach ($order->getItems() as $orderItem) {
            if ($allOrderItems->contains($orderItem)) {
                continue;
            }
            $diff[] = $orderItem;
        }

        foreach ($diff as $orderItem) {
            /** @var Order $order */
            $draft = new AllocationDraft();
            $draft->setOrder($order);
            $draft->setType('Could Not Allocate');
            $allocationDraftItem = new AllocationDraftItem();
            $allocationDraftItem->setOrderItem($orderItem);
            $allocationDraftItem->setProduct($orderItem->getProduct());
            $allocationDraftItem->setProductSku($orderItem->getProductSku());
            $allocationDraftItem->setProductName($orderItem->getProductName());
            $allocationDraftItem->setQuantity($orderItem->getQuantity());
            $draft->addItem($allocationDraftItem);
            $em->persist($draft);
        }

        if ($consolidationWarehouse) {
            // create parent allocation
            /** @var Order $order */
            $parentAllocationDraft = new AllocationDraft();
            $parentAllocationDraft->setOrder($order);
            $parentAllocationDraft->setType('On Hand');
            $parentAllocationDraft->setWarehouse($consolidationWarehouse);
            $parentAllocationDraft->setShippingAddress($consolidationWarehouse->getAddress());
            foreach ($allItems as $item) {
                $parentAllocationDraft->addItem($item);
            }

            foreach ($subAllocations as $subAllocation) {
                $parentAllocationDraft->addChild($subAllocation);
            }

            $em->persist($parentAllocationDraft);
        }

        $em->flush();
    }

    /**
     * @param $order
     * @return Warehouse|mixed
     */
    protected function getConsolidationWarehouse($order)
    {
        $consolidationWH = false; //$order->getConsolidationWarehouse()
        if ($consolidationWH) {
            // consolidation WH is already set and we shouldn't override it (could be a pick up from store)
            return $consolidationWH;
        }
        // determine consolidation WH, but first get all eligible warehouses (filtering WH's that are connected to the
        // WHG that is linked to the SalesChannelGroup from the SalesChannel that comes from the order)
        $consolidationWHs = $this->getEligibleWarehouses($order);
        // run DISTANCE WFA RULE ONLY
        if (count($consolidationWHs) > 0) {
            $results = [];
            foreach ($consolidationWHs as $wh) {
                $resultItem[OrderWarehouseResult::WAREHOUSE_FIELD] = $wh;
                $resultItem[OrderWarehouseResult::ORDER_ITEMS_FIELD] = new ArrayCollection([]);
                $results[][] = new OrderWarehouseResult($resultItem);
            }

            // run rule with the 'results'
            $rule = $this->wfaRuleRepository->findBy(['strategy' => MinimumDistanceWFAStrategy::IDENTIFIER]);
            /** @var WFARule[] $filteredRules */
            $filteredRules = $this->rulesFiltrationService
                ->getFilteredRuleOwners($rule);
            foreach ($filteredRules as $filteredRule) {
                $strategy = $this->strategiesRegistry->getStrategy($filteredRule->getStrategy());
                $results = $strategy->getWarehouseResults($order, $results);
            }

            // the result CANNOT BE empty as the eligible warehouses are just being sorted by distance!
            /** @var OrderWarehouseResult[] $firstResult */
            $firstResult = reset($results);
            // below is a bit iffy... tbh
            return array_shift($firstResult)->getWarehouse();
        }

        // DISTANCE WFA rule cannot be used because Google Integration settings and Geoding have not been configured
        // need backup for when WFA rule cannot be used to determine consolidation WH, priority in the group might help? :thinking:?
        // for now just give us the first that is being added from the eligible warehouses....
        return $consolidationWHs->first();
    }

    /**
     * @param $order
     * @return ArrayCollection
     */
    protected function getEligibleWarehouses($order)
    {
        /** @var WarehouseChannelGroupLink $linkOwner */
        $linkOwner = $this->doctrineHelper
            ->getEntityRepositoryForClass(WarehouseChannelGroupLink::class)
            ->findLinkBySalesChannelGroup($order->getSalesChannel()->getGroup());
        $consoWHs = new ArrayCollection();
        // all warehouses are eligible with the exception of External WH's
        // External WH's are used for dropshipping and we don't want to consolidate to a dropshipping WH
        foreach ($linkOwner->getWarehouseGroup()->getWarehouses() as $warehouse) {
            if ($warehouse->getWarehouseType()->getName() === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                continue;
            }

            $consoWHs->add($warehouse);
        }

        return $consoWHs;
    }
}
