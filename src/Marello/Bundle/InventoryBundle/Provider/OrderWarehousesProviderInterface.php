<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\OrderBundle\Entity\Order;

interface OrderWarehousesProviderInterface
{
    // estimation method (public function setEstimation($estimation = false)
    // will be included in 3.0, not in 2.2 because of BC breaks

    /**
     * @param Order $order
     * @param Allocation|null $allocation
     * @return OrderWarehouseResult[]
     */
    public function getWarehousesForOrder(Order $order, Allocation $allocation = null): array;
}
