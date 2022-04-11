<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategiesRegistry;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumDistance\MinimumDistanceWFAStrategy;

class InventoryAllocationProvider
{
    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /** @var OrderWarehousesProviderInterface $warehousesProvider */
    protected $warehousesProvider;

    /** @var WFAStrategiesRegistry $strategiesRegistry */
    protected $strategiesRegistry;

    /** @var RuleFiltrationServiceInterface $rulesFiltrationService */
    protected $rulesFiltrationService;

    /** @var WFARuleRepository $wfaRuleRepository */
    protected $wfaRuleRepository;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /**
     * InventoryAllocationProvider constructor.
     * @param DoctrineHelper $doctrineHelper
     * @param OrderWarehousesProviderInterface $warehousesProvider
     * @param WFAStrategiesRegistry $strategiesRegistry
     * @param RuleFiltrationServiceInterface $rulesFiltrationService
     * @param WFARuleRepository $wfaRuleRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        OrderWarehousesProviderInterface $warehousesProvider,
        WFAStrategiesRegistry $strategiesRegistry,
        RuleFiltrationServiceInterface $rulesFiltrationService,
        WFARuleRepository $wfaRuleRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->warehousesProvider = $warehousesProvider;
        $this->strategiesRegistry = $strategiesRegistry;
        $this->rulesFiltrationService = $rulesFiltrationService;
        $this->wfaRuleRepository = $wfaRuleRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Order $order
     * @param Allocation|null $allocation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function allocateOrderToWarehouses(Order $order, Allocation $allocation = null)
    {
        // check if order needs to be consolidated
        $consolidation = $order->isConsolidationEnabled();
        $consolidationWarehouse = null;
        if ($consolidation) {
            $consolidationWarehouse = $this->getConsolidationWarehouse($order);
        }

        // consolidation is not enabled, so we just run the 'normal' WFA rules (all of them)
        // the result of the WFA rules is also the input for the Allocation/AllocationItems
        // create all allocation/allocation items
        $allOrderItems = new ArrayCollection();
        $allItems = [];
        $subAllocations = [];
        $em = $this->doctrineHelper->getEntityManagerForClass(Allocation::class);
        foreach ($this->warehousesProvider->getWarehousesForOrder($order, $allocation) as $orderWarehouseResults) {
            if ($allocation && $allocation->getWarehouse()) {
                // release current allocated inventory if there is a warehouse
                $allocation->getItems()->map(function (AllocationItem $item) use ($allocation, $order) {
                    $this->handleInventoryUpdate(
                        $item->getOrderItem(),
                        null,
                        -$item->getQuantity(),
                        'inventory_allocation.released',
                        $allocation->getWarehouse()
                    );
                });
            }
            foreach ($orderWarehouseResults as $result) {
                /** @var Order $order */
                $allocation = new Allocation();
                $allocation->setOrder($order);
                $allocation->setType('on_hand');

                // find allocation by warehouse
                if ($result->getWarehouse()->getCode() === 'no_warehouse') {
                    $allocation->setType('waiting_for_supply');
                }
                if ($result->getWarehouse()->getCode() === 'could_not_allocate') {
                    $allocation->setType('could_not_allocate');
                }
                if (!in_array($result->getWarehouse()->getCode(), ['no_warehouse', 'could_not_allocate'])) {
                    $allocation->setWarehouse($result->getWarehouse());
                }
                $shippingAddress = $order->getShippingAddress();
                if ($consolidationWarehouse) {
                    // this is a question mark...
                    $shippingAddress = $consolidationWarehouse->getAddress();
                }

                $allocation->setShippingAddress($shippingAddress);
                $itemWithQty = $result->getItemsWithQuantity();
                foreach ($result->getOrderItems() as $item) {
                    $allocationItem = new AllocationItem();
                    $orderItem = $item;
                    if ($item instanceof AllocationItem) {
                        $orderItem = $item->getOrderItem();
                    }
                    $allocationItem->setOrderItem($orderItem);
                    $allocationItem->setProduct($item->getProduct());
                    $allocationItem->setProductSku($item->getProductSku());
                    $allocationItem->setProductName($item->getProductName());
                    if ($allocation->getWarehouse()) {
                        $allocationItem->setWarehouse($allocation->getWarehouse());
                    }
                    $allocationItem->setQuantity($itemWithQty[$item->getProductSku()]);
                    $allocation->addItem($allocationItem);
                    $allOrderItems->add($item);
                    if ($consolidationWarehouse) {
                        $allItems[] = clone $allocationItem;
                        $subAllocations[] = $allocation;
                    }
                }

                $em->persist($allocation);
                if ($allocation->getWarehouse()) {
                    $allocation->getItems()->map(function (AllocationItem $item) use ($allocation) {
                        $this->handleInventoryUpdate(
                            $item->getOrderItem(),
                            null,
                            $item->getQuantity(),
                            'inventory_allocation.allocated',
                            $allocation->getWarehouse()
                        );
                    });
                }
            }
        }

        if (!$allocation) {
            $diff = [];
            foreach ($order->getItems() as $orderItem) {
                if ($allOrderItems->contains($orderItem)) {
                    continue;
                }
                $diff[] = $orderItem;
            }

            foreach ($diff as $orderItem) {
                /** @var Order $order */
                $diffAllocation = new Allocation();
                $diffAllocation->setOrder($order);
                $diffAllocation->setType('could_not_allocate');
                $allocationItem = new AllocationItem();
                $allocationItem->setOrderItem($orderItem);
                $allocationItem->setProduct($orderItem->getProduct());
                $allocationItem->setProductSku($orderItem->getProductSku());
                $allocationItem->setProductName($orderItem->getProductName());
                $allocationItem->setQuantity($orderItem->getQuantity());
                $diffAllocation->addItem($allocationItem);
                $em->persist($diffAllocation);
            }
        }

        if ($consolidationWarehouse) {
            // create parent allocation
            /** @var Order $order */
            $parentAllocation = new Allocation();
            $parentAllocation->setOrder($order);
            $parentAllocation->setType('on_hand');
            $parentAllocation->setWarehouse($consolidationWarehouse);
            $parentAllocation->setShippingAddress($order->getShippingAddress());
            foreach ($allItems as $item) {
                $parentAllocation->addItem($item);
            }

            foreach ($subAllocations as $subAllocation) {
                $parentAllocation->addChild($subAllocation);
            }

            $em->persist($parentAllocation);
        }

        $em->flush();
    }

    /**
     * handle the inventory update for items which have been picked and packed
     * @param OrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param Warehouse $warehouse
     */
    protected function handleInventoryUpdate(
        $item,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $message,
        $warehouse
    ) {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            $message
        );

        $context->setValue('warehouse', $warehouse);
        $context->setValue('forceFlush', true);
        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );
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
                $results = $strategy->getWarehouseResults($order, null, $results);
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
