<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;

interface OrderWarehousesProviderInterface
{
    /**
     * @param Order $order
     * @param Allocation|null $allocation
     * @return OrderWarehouseResult[]
     */
    public function getWarehousesForOrder(Order $order, Allocation $allocation = null): array;
}
