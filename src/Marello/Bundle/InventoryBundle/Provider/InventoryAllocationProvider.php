<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;

use Marello\Bundle\OrderBundle\Model\OrderStatusesInterface;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class InventoryAllocationProvider
{
    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /** @var OrderWarehousesProviderInterface $warehousesProvider */
    protected $warehousesProvider;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /** @var ArrayCollection $allOrderItems */
    protected $allOrderItems;

    /** @var array $allItems */
    protected $allItems = [];

    /** @var array $subAllocations */
    protected $subAllocations = [];

    /**
     * InventoryAllocationProvider constructor.
     * @param DoctrineHelper $doctrineHelper
     * @param OrderWarehousesProviderInterface $warehousesProvider
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        OrderWarehousesProviderInterface $warehousesProvider,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->warehousesProvider = $warehousesProvider;
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
        $this->allOrderItems = new ArrayCollection();

        $em = $this
            ->getDoctrineHelper()
            ->getEntityManagerForClass(Allocation::class);
        foreach ($this->warehousesProvider->getWarehousesForOrder($order, $allocation) as $orderWarehouseResults) {
            if ($allocation && $allocation->getWarehouse()) {
                $this->handleAllocationInventory($allocation, $order, true);
            }

            /** @var OrderWarehouseResult $result */
            foreach ($orderWarehouseResults as $result) {
                /** @var Order $order */
                $newAllocation = new Allocation();
                $newAllocation->setOrder($order);
                $newAllocation->setOrganization($order->getOrganization());
                $newAllocation->setState($this->getEnumValue('marello_allocation_state', AllocationStateStatusInterface::ALLOCATION_STATE_AVAILABLE));
                $newAllocation->setStatus($this->getEnumValue('marello_allocation_status', AllocationStateStatusInterface::ALLOCATION_STATUS_ON_HAND));

                // find allocation by warehouse
                if ($result->getWarehouse()->getCode() === 'no_warehouse') {
                    $newAllocation->setState($this->getEnumValue('marello_allocation_state', AllocationStateStatusInterface::ALLOCATION_STATE_WFS));
                    $newAllocation->setStatus($this->getEnumValue('marello_allocation_status', AllocationStateStatusInterface::ALLOCATION_STATUS_CNA));
                }
                if ($result->getWarehouse()->getCode() === AllocationStateStatusInterface::ALLOCATION_STATUS_CNA) {
                    $newAllocation->setState($this->getEnumValue('marello_allocation_state', AllocationStateStatusInterface::ALLOCATION_STATE_ALERT));
                    $newAllocation->setStatus($this->getEnumValue('marello_allocation_status', AllocationStateStatusInterface::ALLOCATION_STATUS_CNA));
                }

                if ($result->getWarehouse()->getWarehouseType()->getName() === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                    $newAllocation->setStatus($this->getEnumValue('marello_allocation_status', AllocationStateStatusInterface::ALLOCATION_STATUS_DROPSHIP));
                }

                if (!in_array($result->getWarehouse()->getCode(), ['no_warehouse', 'could_not_allocate'])) {
                    $newAllocation->setWarehouse($result->getWarehouse());
                }

                $shippingAddress = $this->getShippingAddress($order);
                $newAllocation->setShippingAddress($shippingAddress);

                $this->createAllocationItems($result, $newAllocation);
                // allocation has been rejected or needs to be reallocated
                // set allocation as the source for the new allocation for reference
                if ($allocation) {
                    $newAllocation->setSourceEntity($allocation);
                }
                $em->persist($newAllocation);
                $em->flush($newAllocation);

                if ($newAllocation->getWarehouse()) {
                    $this->handleAllocationInventory($newAllocation);
                }
            }
        }

        if (!$allocation) {
            $diff = [];
            foreach ($order->getItems() as $orderItem) {
                if ($this->allOrderItems->contains($orderItem)) {
                    continue;
                }
                $diff[] = $orderItem;
            }

            foreach ($diff as $orderItem) {
                /** @var Order $order */
                $diffAllocation = new Allocation();
                $diffAllocation->setOrder($order);
                $diffAllocation->setStatus('could_not_allocate');
                $diffAllocation->setState($this->getEnumValue('marello_allocation_state', AllocationStateStatusInterface::ALLOCATION_STATE_ALERT));
                $diffAllocation->setStatus($this->getEnumValue('marello_allocation_status', AllocationStateStatusInterface::ALLOCATION_STATUS_CNA));
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

            $allocationItem->setOrganization($orderItem->getOrganization());
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
            $this->allItems[] = clone $allocationItem;
            $this->subAllocations[] = $allocation;
        }
    }

    /**
     * @return DoctrineHelper
     */
    public function getDoctrineHelper()
    {
        return $this->doctrineHelper;
    }

    /**
     * @return array
     */
    public function getAllItems()
    {
        return $this->allItems;
    }

    /**
     * @return array
     */
    public function getAllSubAllocations()
    {
        return $this->subAllocations;
    }

    /**
     * @param Order $order
     * @return MarelloAddress|null
     */
    protected function getShippingAddress(Order $order)
    {
        return $order->getShippingAddress();
    }

    /**
     * @param $enumClass
     * @param $value
     * @return object|null
     */
    protected function getEnumValue($enumClass, $value)
    {
        $className = ExtendHelper::buildEnumValueClassName($enumClass);
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $this->doctrineHelper
            ->getEntityManagerForClass($className)
            ->getRepository($className);

        return $enumRepo->findOneBy(['id' => $value]);
    }

    /**
     * @param Allocation $allocation
     * @param Order|null $order
     * @param false $release
     */
    protected function handleAllocationInventory(Allocation $allocation, Order $order = null, $release = false)
    {
        if ($order && $release) {
            // release current allocated inventory if there is a warehouse
            $allocation->getItems()->map(function (AllocationItem $item) use ($allocation, $order) {
                $this->handleInventoryUpdate(
                    $item->getOrderItem(),
                    null,
                    -$item->getQuantity(),
                    'inventory_allocation.released',
                    $allocation->getWarehouse(),
                    $allocation
                );
            });
        }

        if (!$release) {
            // allocate inventory for allocation
            $allocation->getItems()->map(function (AllocationItem $item) use ($allocation) {
                $this->handleInventoryUpdate(
                    $item->getOrderItem(),
                    null,
                    $item->getQuantity(),
                    'inventory_allocation.allocated',
                    $allocation->getWarehouse(),
                    $allocation
                );
            });
        }
    }

    /**
     * handle the inventory update for items which have been picked and packed
     * @param OrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param $message
     * @param Warehouse $warehouse
     * @param Allocation $allocation
     */
    protected function handleInventoryUpdate(
        OrderItem $item,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $message,
        Warehouse $warehouse,
        Allocation $allocation
    ) {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            $message,
            $allocation
        );

        $context->setValue('warehouse', $warehouse);
        $context->setValue('forceFlush', true);
        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }
}
