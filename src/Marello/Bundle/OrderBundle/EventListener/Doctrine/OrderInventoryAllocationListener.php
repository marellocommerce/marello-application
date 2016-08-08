<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class OrderInventoryAllocationListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Order) {
            return;
        }

        $entity->getItems()->map(function (OrderItem $item) use ($entity, $args) {
            /** @var InventoryItem $inventoryItem */
            $inventoryItem = $item->getProduct()->getInventoryItems()->first();
            $inventoryItem->adjustStockLevels('order_workflow.pending', null, $item->getQuantity(), null, $entity);

            $args->getEntityManager()->persist($inventoryItem);
        });
    }
}
