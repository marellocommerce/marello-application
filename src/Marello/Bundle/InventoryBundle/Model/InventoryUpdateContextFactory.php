<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;

class InventoryUpdateContextFactory
{
    /**
     * @param ProductAwareInterface||ProductInterface $entity
     * @param InventoryItem $inventoryItem
     * @param int $inventoryUpdateQty
     * @param int $allocatedInventoryUpdateQty
     * @param string $trigger
     * @param null $relatedEntity
     * @param bool $virtual
     * @return InventoryUpdateContext|null
     */
    public static function createInventoryUpdateContext(
        $entity,
        $inventoryItem,
        $inventoryUpdateQty,
        $allocatedInventoryUpdateQty,
        $trigger,
        $relatedEntity = null,
        $virtual = false
    ) {
        if (!$entity instanceof ProductInterface && $entity instanceof ProductAwareInterface) {
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
            ->setIsVirtual($virtual)
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
     * @param bool $virtual
     * @return InventoryUpdateContext
     */
    public static function createInventoryLevelUpdateContext(
        InventoryLevel $inventoryLevel,
        $inventoryItem,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $trigger,
        $relatedEntity = null,
        $virtual = false
    ) {
        $context = self::create();
        $context
            ->setInventory((int)$inventoryUpdateQty)
            ->setAllocatedInventory((int)$allocatedInventoryQty)
            ->setChangeTrigger($trigger)
            ->setInventoryLevel($inventoryLevel)
            ->setInventoryItem($inventoryItem)
            ->setRelatedEntity($relatedEntity)
            ->setIsVirtual($virtual)
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
