<?php

namespace Marello\Bundle\SubscriptionBundle\EventListener;

use Marello\Bundle\OrderBundle\Event\ProductAvailableInventoryValidationEvent;

class ProductAvailableInventoryValidationListener
{
    public function onValidation(ProductAvailableInventoryValidationEvent $event)
    {
        $violation = $event->getViolation();
        if ($violation === true) {
            $orderItem = $event->getOrderItem();
            $product = $orderItem->getProduct();
            if ($product->getType() === 'subscription') {
                $event->setViolation(false);
            }
        }
    }
}
