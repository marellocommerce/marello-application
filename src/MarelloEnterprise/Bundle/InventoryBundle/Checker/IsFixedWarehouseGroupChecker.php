<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Checker;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Migrations\Data\ORM\LoadWarehouseTypeData;

class IsFixedWarehouseGroupChecker
{
    /**
     * @param WarehouseGroup $group
     * @return bool
     */
    public function check(WarehouseGroup $group)
    {
        $warehouses = $group->getWarehouses();

        /** @var Warehouse $firstWarehouse */
        $firstWarehouse = $warehouses->first();
        if ($warehouses->count() === 1 &&
            $firstWarehouse->getWarehouseType()->getName() === LoadWarehouseTypeData::FIXED_TYPE) {
            return true;
        }
        
        return false;
    }
}
