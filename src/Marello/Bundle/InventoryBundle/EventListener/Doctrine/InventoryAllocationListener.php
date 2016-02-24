<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryAllocation;

class InventoryAllocationListener
{

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof InventoryAllocation) {
            return;
        }

        $inventoryItem = $entity->getInventoryItem();
        $inventoryItem->modifyAllocatedQuantity($entity->getQuantity());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof InventoryAllocation) {
            return;
        }

        $entity->getInventoryItem()->modifyAllocatedQuantity(-$entity->getQuantity());
        $args->getEntityManager()->persist($entity->getInventoryItem());
    }
}
