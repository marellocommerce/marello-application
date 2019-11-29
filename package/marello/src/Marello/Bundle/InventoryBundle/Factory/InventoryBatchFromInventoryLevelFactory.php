<?php

namespace Marello\Bundle\InventoryBundle\Factory;

use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

class InventoryBatchFromInventoryLevelFactory
{
    /**
     * @param InventoryLevel $inventoryLevel
     * @return InventoryBatch
     */
    public static function createInventoryBatch(InventoryLevel $inventoryLevel)
    {
        $inventoryBatch = new InventoryBatch();
        $inventoryBatch
            ->setInventoryLevel($inventoryLevel)
            ->setQuantity($inventoryLevel->getInventoryQty());

        return $inventoryBatch;
    }
}