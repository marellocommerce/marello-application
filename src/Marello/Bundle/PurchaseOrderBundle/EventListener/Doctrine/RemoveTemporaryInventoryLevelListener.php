<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;

class RemoveTemporaryInventoryLevelListener
{
    public function postUpdate(InventoryLevel $inventoryLevel, LifecycleEventArgs $args)
    {
        if ($inventoryLevel->getInventoryLevelLogRecords()->count() === 0) {
            return;
        }

        // Check that the inventory level relates to a temporary warehouse related to a purchase order
        if (!str_starts_with($inventoryLevel->getWarehouse()->getCode(), PurchaseOrder::TEMPORARY_WAREHOUSE_PREFIX)) {
            return;
        }

        if ($inventoryLevel->getInventoryQty() !== 0 || $inventoryLevel->getAllocatedInventoryQty() !== 0) {
            return;
        }

        $args->getEntityManager()->remove($inventoryLevel);
        $args->getEntityManager()->flush();
    }
}
