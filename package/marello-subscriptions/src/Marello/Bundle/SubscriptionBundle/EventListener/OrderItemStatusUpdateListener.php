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
            if ($this->hasInventory($product) === false) {
                $event->setStatusName(LoadOrderItemSubscriptionStatus::SUBSCRIPTION);
            }
        }
    }
    
    private function hasInventory(Product $product)
    {
        if (count($product->getInventoryItems()) === 0) {
            return false;
        }
        foreach ($product->getInventoryItems() as $inventoryItem) {
            if (count($inventoryItem->getInventoryLevels()) > 0) {
                return true;
            }
        }
        
        return false;
    }
}
