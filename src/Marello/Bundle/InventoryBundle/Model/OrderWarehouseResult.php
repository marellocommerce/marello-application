<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Component\HttpFoundation\ParameterBag;

class OrderWarehouseResult extends ParameterBag
{
    const WAREHOUSE_FIELD = 'warehouse';
    const ORDER_ITEMS_FIELD = 'orderItems';
    const ITEMS_WITH_QUANTITY_FIELD = 'quantityFields';

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->get(self::WAREHOUSE_FIELD, false);
    }

    /**
     * @return ArrayCollection|OrderItem[]
     */
    public function getOrderItems()
    {
        return $this->get(self::ORDER_ITEMS_FIELD, false);
    }

    /**
     * @return array
     */
    public function getItemsWithQuantity()
    {
        return $this->get(self::ITEMS_WITH_QUANTITY_FIELD, false);
    }
}
