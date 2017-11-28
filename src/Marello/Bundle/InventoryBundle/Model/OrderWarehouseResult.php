<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Component\HttpFoundation\ParameterBag;

class OrderWarehouseResult extends ParameterBag
{
    const WAREHOUSE_FIELD = 'warehouse';
    const ORDER_ITEMS_FIELD = 'orderItems';

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->get(self::WAREHOUSE_FIELD, false);
    }

    /**
     * @return OrderItem[]
     */
    public function getOrderItems()
    {
        return $this->get(self::ORDER_ITEMS_FIELD, false);
    }
}
