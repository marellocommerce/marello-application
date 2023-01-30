<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class OrderItemOriginalPriceListener
{
    public function prePersist(OrderItem $orderItem, LifecycleEventArgs $args): void
    {
        $this->setDefaultPrice($orderItem);
    }

    private function setDefaultPrice(OrderItem $orderItem): void
    {
        $channel = $orderItem->getOrder()->getSalesChannel();
        $priceList = $orderItem->getProduct()->getSalesChannelPrice($channel);
        $orderItem->setOriginalPriceInclTax($priceList->getDefaultPrice()->getValue());
    }
}
