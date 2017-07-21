<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryItemAwareInterface;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\ProductInventoryAwareInterface;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;

class InventoryUpdateContextFactory
{
    /**
     * @param ProductInventoryAwareInterface||ProductInterface $entity
     * @param InventoryItem $inventoryItem
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryUpdateQty
     * @param $trigger
     * @param $relatedEntity
     * @return InventoryUpdateContext|null
     */
    public static function createInventoryUpdateContext(
        $entity,
        $inventoryItem = null,
        $inventoryUpdateQty,
        $allocatedInventoryUpdateQty,
        $trigger,
        $relatedEntity = null
    ) {
        if (!$entity instanceof ProductInterface && $entity instanceof ProductInventoryAwareInterface) {
            $entity = $entity->getProduct();
        }

        $context = self::create();
        $context
            ->setInventory((int)$inventoryUpdateQty)
            ->setAllocatedInventory((int)$allocatedInventoryUpdateQty)
            ->setChangeTrigger($trigger)
            ->setProduct($entity)
            ->setInventoryItem($inventoryItem)
            ->setRelatedEntity($relatedEntity)
        ;

        return $context;

    }

    /**
     * @param InventoryLevel $inventoryLevel
     * @param null $inventoryItem
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param $trigger
     * @param null $relatedEntity
     * @return InventoryUpdateContext
     */
    public static function createInventoryLevelUpdateContext(
        InventoryLevel $inventoryLevel,
        $inventoryItem = null,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $trigger,
        $relatedEntity = null
    ) {
        $context = self::create();
        $context
            ->setInventory((int)$inventoryUpdateQty)
            ->setAllocatedInventory((int)$allocatedInventoryQty)
            ->setChangeTrigger($trigger)
            ->setInventoryLevel($inventoryLevel)
            ->setInventoryItem($inventoryItem)
            ->setRelatedEntity($relatedEntity)
        ;

        return $context;
    }

    /**
     * @return InventoryUpdateContext
     */
    private static function create()
    {
        return new InventoryUpdateContext();
    }
}
