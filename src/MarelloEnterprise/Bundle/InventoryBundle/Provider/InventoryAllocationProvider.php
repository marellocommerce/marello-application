<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;

use Marello\Bundle\InventoryBundle\Provider\AllocationContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Strategy\WFA\WFAStrategiesRegistry;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WFARuleRepository;
use Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider as BaseAllocationProvider;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFA\MinimumDistance\MinimumDistanceWFAStrategy;

class InventoryAllocationProvider extends BaseAllocationProvider
{
    /** @var ArrayCollection $allOrderItems */
    protected $allOrderItems;

    /** @var Warehouse $consolidationWarehouse */
    protected $consolidationWarehouse;

    /** @var array $allItems */
    protected $allItems = [];

    /** @var array $subAllocations */
    protected $subAllocations = [];

    public function __construct(
        protected BaseAllocationProvider $baseAllocationProvider,
        protected WFAStrategiesRegistry $strategiesRegistry,
        protected RuleFiltrationServiceInterface $rulesFiltrationService,
        DoctrineHelper $doctrineHelper,
        OrderWarehousesProviderInterface $warehousesProvider,
        EventDispatcherInterface $eventDispatcher,
        protected ConfigManager $configManager
    ) {
        parent::__construct($doctrineHelper, $warehousesProvider, $eventDispatcher);
    }

    public function allocateOrderToWarehouses(
        Order $order,
        Allocation $allocation = null,
        callable $callback = null
    ) {
        $this->consolidationWarehouse = null;
        $shippingAddress = null;
        // check if order needs to be consolidated
        if ($this->isConsolidationEnabled($order)) {
            $this->consolidationWarehouse = $this->getConsolidationWarehouse($order);
            $shippingAddress = clone $order->getShippingAddress();
        }
        // consolidation is not enabled, so we just run the 'normal' WFA rules (all of them)
        // the result of the WFA rules is also the input for the Allocation/AllocationItems
        // create all allocation/allocation items
        $this->baseAllocationProvider->allocateOrderToWarehouses($order, $allocation, $callback);

        $em = $this
            ->baseAllocationProvider
            ->getDoctrineHelper()
            ->getEntityManagerForClass(Allocation::class);

        if ($this->consolidationWarehouse) {
            if (!$allocation) {
                /** @var Order $order */
                $parentAllocation = new Allocation();
                $parentAllocation->setOrder($order);
                $parentAllocation->setOrganization($order->getOrganization());
                $parentAllocation->setState(
                    $this->getEnumValue(
                        AllocationStateStatusInterface::ALLOCATION_STATE_ENUM_CODE,
                        AllocationStateStatusInterface::ALLOCATION_STATE_AVAILABLE
                    )
                );
                $parentAllocation->setStatus(
                    $this->getEnumValue(
                        AllocationStateStatusInterface::ALLOCATION_STATUS_ENUM_CODE,
                        AllocationStateStatusInterface::ALLOCATION_STATUS_ON_HAND
                    )
                );
                $parentAllocation->setAllocationContext(
                    $this->getEnumValue(
                        AllocationContextInterface::ALLOCATION_CONTEXT_ENUM_CODE,
                        AllocationContextInterface::ALLOCATION_CONTEXT_CONSOLIDATION
                    )
                );
                $parentAllocation->setWarehouse($this->consolidationWarehouse);
                $parentAllocation->setShippingAddress($shippingAddress ?? $order->getShippingAddress());
            } else {
                $parentAllocation = $allocation->getParent();
            }

            /** @var AllocationItem $item */
            foreach ($this->baseAllocationProvider->getAllItems() as $item) {
                if ($this->isAllocationExcluded($item->getAllocation())) {
                    continue;
                }
                $parentAllocation->addItem($item);
            }
            /** @var Allocation $subAllocation */
            foreach ($this->baseAllocationProvider->getAllSubAllocations() as $subAllocation) {
                if ($this->isAllocationExcluded($subAllocation)) {
                    continue;
                }
                $subAllocation->setShippingAddress($this->consolidationWarehouse->getAddress());
                $em->persist($subAllocation);
                $parentAllocation->addChild($subAllocation);
            }

            $em->persist($parentAllocation);
        }

        $em->flush();
    }

    /**
     * @param Order $order
     * @return bool
     */
    protected function isConsolidationEnabled(Order $order)
    {
        if (!$this->configManager->get('marello_enterprise_order.enable_order_consolidation')) {
            return false;
        }

        $salesChannelConsolidationEnabled = $this->configManager->get(
            'marello_enterprise_order.enable_order_consolidation',
            false,
            false,
            $order->getSalesChannel()
        );

        return (
            $order->getConsolidationEnabled() ||
            $salesChannelConsolidationEnabled ||
            $this->configManager->get('marello_enterprise_order.set_global_consolidation')
        );
    }

    /**
     * @param Allocation $allocation
     * @return bool
     */
    protected function isAllocationExcluded(Allocation $allocation)
    {
        $isExternalWhType = false;
        if ($allocation->getWarehouse()) {
            $whType = $allocation->getWarehouse()->getWarehouseType()->getName();
            $isExternalWhType = ($whType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);
        }

        $excludedStates = $this->configManager->get(
            'marello_enterprise_inventory.inventory_allocation_consolidation_exclusion'
        );
        return ($isExternalWhType ||
            in_array($allocation->getState()->getName(), $excludedStates)
        );
    }

    /**
     * @param Order $order
     * @return MarelloAddress|null
     */
    protected function getShippingAddress(Order $order)
    {
        $shippingAddress = clone $order->getShippingAddress();
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
        // need backup for when WFA rule cannot be used to determine consolidation WH, priority in the group might help?
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

            if (!$warehouse->getIsConsolidationWarehouse()) {
                continue;
            }

            $consoWHs->add($warehouse);
        }

        return $consoWHs;
    }
}
