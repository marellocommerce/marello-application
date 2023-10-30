<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Manager\InventoryItemManager;

class OrderItemProductUnitListener
{
    /** @var InventoryItemManager $inventoryItemManager  */
    protected $inventoryItemManager;

    /**
     * @param InventoryItemManager $inventoryItemManager
     */
    public function __construct(InventoryItemManager $inventoryItemManager)
    {
        $this->inventoryItemManager = $inventoryItemManager;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof OrderItem && $entity->getProductUnit() === null) {
            // try getting product unit from inventory item
            $inventoryItem = $this->inventoryItemManager->getInventoryItem($entity->getProduct());
            if ($inventoryItem && $uom = $inventoryItem->getProductUnit()) {
                $entity->setProductUnit($uom);
            } else {
                $entity->setProductUnit($this->inventoryItemManager->getDefaultProductUnit());
            }
        }
    }
}
