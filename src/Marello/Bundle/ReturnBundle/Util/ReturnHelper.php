<?php

namespace Marello\Bundle\ReturnBundle\Util;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;

class ReturnHelper
{
    /**
     * Returns amount of already returned items for given order item.
     *
     * @param OrderItem $orderItem
     *
     * @return int
     */
    public function getOrderItemReturnedQuantity(OrderItem $orderItem)
    {
        $sum = 0;

        $orderItem
            ->getReturnItems()
            ->map(function (ReturnItem $returnItem) use (&$sum) {
                $sum += $returnItem->getQuantity();
            });

        return $sum;
    }
}
