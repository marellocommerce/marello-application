<?php

namespace Marello\Bundle\OrderBundle\Provider\OrderItem;

use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Event\OrderItemsForNotificationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ShippingPreparedOrderItemsForNotificationProvider
{
    /**
     * @var OrderWarehousesProviderInterface
     */
    private $orderWarehousesProvider;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    
    /**
     * @param OrderWarehousesProviderInterface $orderWarehousesProvider
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        OrderWarehousesProviderInterface $orderWarehousesProvider,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->orderWarehousesProvider = $orderWarehousesProvider;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    /**
     * @inheritDoc
     */
    public function getItems(Order $order, $templateName)
    {
        $orderWarehousesResults = $this->orderWarehousesProvider->getWarehousesForOrder($order);
        $itemsInWarehouses = [];
        foreach ($orderWarehousesResults as $orderWarehouseResult) {
            foreach ($orderWarehouseResult->getOrderItems() as $orderItem) {
                $itemsInWarehouses[$orderItem->getId()] = $orderItem;
            }
        }
        $event = new OrderItemsForNotificationEvent($itemsInWarehouses, $templateName);
        $this->eventDispatcher->dispatch($event, OrderItemsForNotificationEvent::NAME);
        return $event->getOrderItems();
    }
}
