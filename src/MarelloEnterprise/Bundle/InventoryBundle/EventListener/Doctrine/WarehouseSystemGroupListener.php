<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;

class WarehouseSystemGroupListener
{
    /**
     * @param Warehouse $warehouse
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Warehouse $warehouse, LifecycleEventArgs $args)
    {
        $systemGroup = $args
            ->getEntityManager()
            ->getRepository(WarehouseGroup::class)
            ->findOneBy(['system' => true]);

        if ($systemGroup) {
            $warehouse->setGroup($systemGroup);
        }
    }
}
