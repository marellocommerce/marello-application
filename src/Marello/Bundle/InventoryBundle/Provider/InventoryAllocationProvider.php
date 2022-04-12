<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;

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

        $em = $this->getAllocationEntityManager();
        foreach ($this->warehousesProvider->getWarehousesForOrder($order, $allocation) as $orderWarehouseResults) {
            if ($allocation && $allocation->getWarehouse()) {
                $this->handleAllocationInventory($allocation, $order, true);
            }

            /** @var OrderWarehouseResult $result */
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

                $shippingAddress = $this->getShippingAddress($order);
                $allocation->setShippingAddress($shippingAddress);

                $this->createAllocationItems($result, $allocation);
                $em->persist($allocation);

                if ($allocation->getWarehouse()) {
                    $this->handleAllocationInventory($allocation);
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

        $em->flush();
    }

    /**
     * @param OrderWarehouseResult $result
     * @param Allocation $allocation
     */
    protected function createAllocationItems(OrderWarehouseResult $result, Allocation $allocation)
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
     * @param Order $order
     * @return MarelloAddress|null
     */
    protected function getShippingAddress(Order $order)
    {
        return $order->getShippingAddress();
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
                    $allocation->getWarehouse()
                );
            });
        } else {
            // allocate inventory for allocation
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
}
