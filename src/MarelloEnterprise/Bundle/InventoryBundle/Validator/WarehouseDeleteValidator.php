<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class WarehouseDeleteValidator
{
    /**
     * Check if the Warehouse is currently marked as default
     *
     * @param Warehouse $warehouse
     * @return bool
     */
    public function validate(Warehouse $warehouse)
    {
        return (!$warehouse->isDefault());
    }
}
