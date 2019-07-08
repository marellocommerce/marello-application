<?php

namespace Marello\Bundle\SubscriptionBundle\EventListener;

use Marello\Bundle\OrderBundle\Event\OrderItemStatusUpdateEvent;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadOrderItemSubscriptionStatus;

class OrderItemStatusUpdateListener
{
    /**
     * @param OrderItemStatusUpdateEvent $event
     */
    public function onStatusUpdate(OrderItemStatusUpdateEvent $event)
    {
        $item = $event->getOrderItem();
        $product = $item->getProduct();
        if ($product->getType() === LoadOrderItemSubscriptionStatus::SUBSCRIPTION) {
            $event->setStatusName(LoadOrderItemSubscriptionStatus::SUBSCRIPTION);
        }
    }
}
