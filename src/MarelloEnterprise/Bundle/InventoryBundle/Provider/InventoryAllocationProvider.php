<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use Marello\Bundle\InventoryBundle\Strategy\WFA\WFAStrategiesRegistry;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFA\MinimumDistance\MinimumDistanceWFAStrategy;
use Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider as BaseAllocationProvider;

class InventoryAllocationProvider extends BaseAllocationProvider
{
    /** @var WFAStrategiesRegistry $strategiesRegistry */
    protected $strategiesRegistry;

    /** @var RuleFiltrationServiceInterface $rulesFiltrationService */
    protected $rulesFiltrationService;

    /** @var ArrayCollection $allOrderItems */
    protected $allOrderItems;

    /** @var Warehouse $consolidationWarehouse */
    protected $consolidationWarehouse;

    /** @var array $allItems */
    protected $allItems = [];

    /** @var array $subAllocations */
    protected $subAllocations = [];

    /** @var BaseAllocationProvider $baseAllocationProvider */
    protected $baseAllocationProvider;

    /**
     * InventoryAllocationProvider constructor.
     * @param InventoryAllocationProvider $allocationProvider
     * @param WFAStrategiesRegistry $strategiesRegistry
     * @param RuleFiltrationServiceInterface $rulesFiltrationService
     */
    public function __construct(
        BaseAllocationProvider $allocationProvider,
        WFAStrategiesRegistry $strategiesRegistry,
        RuleFiltrationServiceInterface $rulesFiltrationService,
        DoctrineHelper $doctrineHelper,
        OrderWarehousesProviderInterface $warehousesProvider,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($doctrineHelper, $warehousesProvider, $eventDispatcher);
        $this->baseAllocationProvider = $allocationProvider;
        $this->strategiesRegistry = $strategiesRegistry;
        $this->rulesFiltrationService = $rulesFiltrationService;
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
        $this->consolidationWarehouse = null;
        if ($consolidation) {
            $this->consolidationWarehouse = $this->getConsolidationWarehouse($order);
        }
        // consolidation is not enabled, so we just run the 'normal' WFA rules (all of them)
        // the result of the WFA rules is also the input for the Allocation/AllocationItems
        // create all allocation/allocation items
        $this->baseAllocationProvider->allocateOrderToWarehouses($order, $allocation);

        $em = $this
            ->baseAllocationProvider
            ->getDoctrineHelper()
            ->getEntityManagerForClass(Allocation::class);

        if ($this->consolidationWarehouse) {
            // create parent allocation
            /** @var Order $order */
            $parentAllocation = new Allocation();
            $parentAllocation->setOrder($order);
            $parentAllocation->setState($this->getEnumValue('marello_allocation_state', 'available'));
            $parentAllocation->setStatus($this->getEnumValue('marello_allocation_status', 'on_hand'));
            $parentAllocation->setWarehouse($this->consolidationWarehouse);
            $parentAllocation->setShippingAddress($order->getShippingAddress());
            foreach ($this->baseAllocationProvider->getAllItems() as $item) {
                $parentAllocation->addItem($item);
            }
            foreach ($this->baseAllocationProvider->getAllSubAllocations() as $subAllocation) {
                $parentAllocation->addChild($subAllocation);
            }

            $em->persist($parentAllocation);
        }

        $em->flush();
    }

    /**
     * @param OrderWarehouseResult $result
     * @param Allocation $allocation
     */
    public function createAllocationItems(OrderWarehouseResult $result, Allocation $allocation)
    {
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
            $this->allOrderItems->add($orderItem);
            if ($this->consolidationWarehouse) {
                $this->allItems[] = clone $allocationItem;
                $this->subAllocations[] = $allocation;
            }
        }
    }

    /**
     * @param Order $order
     * @return MarelloAddress|null
     */
    protected function getShippingAddress(Order $order)
    {
        $shippingAddress =  $order->getShippingAddress();
        if ($this->consolidationWarehouse) {
            // this is a question mark...
            $shippingAddress = $this->consolidationWarehouse->getAddress();
        }
        return $shippingAddress;
    }

    /**
     * @param Order $order
     * @return Warehouse|mixed
     */
    protected function getConsolidationWarehouse(Order $order)
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

            /** @var WFARuleRepository $wfaRuleRepository */
            $wfaRuleRepository = $this
                ->baseAllocationProvider
                ->getDoctrineHelper()
                ->getEntityRepositoryForClass(WFARule::class);
            // run rule with the 'results'
            $rule = $wfaRuleRepository->findBy(['strategy' => MinimumDistanceWFAStrategy::IDENTIFIER]);
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
     * @param Order $order
     * @return ArrayCollection
     */
    protected function getEligibleWarehouses(Order $order)
    {
        /** @var WarehouseChannelGroupLink $linkOwner */
        $linkOwner = $this->baseAllocationProvider
            ->getDoctrineHelper()
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
