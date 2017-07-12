<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryItemAwareInterface;
use Marello\Bundle\InventoryBundle\Entity\ProductInventoryAwareInterface;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;

class InventoryUpdateContextFactory
{
    /**
     * @param ProductInventoryAwareInterface||ProductInterface $entity
     * @param InventoryItem $inventoryItem
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param $trigger
     * @param $relatedEntity
     * @return InventoryUpdateContext|null
     */
    public static function createInventoryUpdateContext(
        $entity,
        $inventoryItem = null,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $trigger,
        $relatedEntity = null
    ) {
        $inventoryItemData = [];
        if ($entity instanceof ProductInventoryAwareInterface) {
            $inventoryItemData[] = self::getInventoryItemDataFromInterface($entity, $inventoryUpdateQty, $allocatedInventoryQty);
        }

        if ($entity instanceof ProductInterface) {
            $inventoryItemData[] = self::getInventoryItemData($entity, $inventoryUpdateQty, $allocatedInventoryQty);
        }


        if (!$inventoryItemData) {
            return null;
        }

        $context = new InventoryUpdateContext();
        $context
            ->setInventory($inventoryUpdateQty)
            ->setAllocatedInventory($allocatedInventoryQty)
            ->setChangeTrigger($trigger)
            ->setItems($inventoryItemData)
            ->setInventoryItem($inventoryItem)
            ->setRelatedEntity($relatedEntity)
        ;

        return $context;

    }

    /**
     * @param ProductInventoryAwareInterface $entity
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @return array
     */
    protected static function getInventoryItemDataFromInterface(
        ProductInventoryAwareInterface $entity,
        $inventoryUpdateQty,
        $allocatedInventoryQty
    ) {
        $product = $entity->getProduct();
        return self::getInventoryItemData($product, $inventoryUpdateQty, $allocatedInventoryQty);
    }

    /**
     * @param ProductInterface $product
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @return array
     */
    protected static function getInventoryItemData(
        ProductInterface $product,
        $inventoryUpdateQty,
        $allocatedInventoryQty
    ) {
        return [
            'item'          => $product,
            'qty'           => $inventoryUpdateQty,
            'allocatedQty'  => $allocatedInventoryQty
        ];
    }
}
