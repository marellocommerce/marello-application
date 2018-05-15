<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;

interface WarehouseAwareInterface
{
    /**
     * @return Warehouse
     */
    public function getWarehouse();
}