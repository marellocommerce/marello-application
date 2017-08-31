<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Symfony\Component\HttpFoundation\ParameterBag;

class OrderWarehouseResult extends ParameterBag
{
    const WAREHOUSE_FIELD = 'warehouse';
    const ORDER_ITEMS_FIELD = 'orderItems';
    
    public function getWarehouse()
    {
        return $this->get(self::WAREHOUSE_FIELD, false);
    }

    public function getOrderItems()
    {
        return $this->get(self::ORDER_ITEMS_FIELD, false);
    }
}