<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;

interface OrderWarehousesProviderInterface
{
    /**
     * @param Order $order
     * @return OrderWarehouseResult[]
     */
    public function getWarehousesForOrder(Order $order);
}
