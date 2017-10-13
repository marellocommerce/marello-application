<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;

class WarehouseGroupRemoveListener
{
    /**
     * @param WarehouseGroup $warehouseGroup
     * @param LifecycleEventArgs $args
     */
    public function preRemove(WarehouseGroup $warehouseGroup, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $systemGroup = $em
            ->getRepository(WarehouseGroup::class)
            ->findSystemWarehouseGroup();

        if ($systemGroup) {
            $warehouses = $warehouseGroup->getWarehouses();
            foreach ($warehouses as $warehouse) {
                $warehouse->setGroup($systemGroup);
                $em->persist($warehouse);
            }
            $em->flush();
        }
    }
}
