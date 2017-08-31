<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\OrderBundle\Entity\Order;

interface WFAStrategyInterface
{
    /**
     * @return string|int
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param Order $order
     * @return Warehouse[]|null
     */
    public function getWarehouses(Order $order);
}
