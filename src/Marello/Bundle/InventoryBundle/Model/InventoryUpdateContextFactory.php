<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryItemAwareInterface;
use Marello\Bundle\InventoryBundle\Entity\ProductInventoryAwareInterface;

class InventoryUpdateContextFactory
{
    /**
     * @param InventoryItemAwareInterface | InventoryItem $entity
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param $trigger
     * @param $relatedEntity
     * @return InventoryUpdateContext|null
     */
    public static function createInventoryUpdateContext(
        $entity,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $trigger,
        $relatedEntity = null
    ) {

        if (!$entity instanceof ProductInventoryAwareInterface) {
            return null;
        }

        /*
         * Decides how to format data depending on type of parameter
         */
        $inventoryItemData = null;
        if ($entity instanceof ProductInventoryAwareInterface) {
            $inventoryItemData = self::getInventoryItemDataFromInterface($entity, $inventoryUpdateQty, $allocatedInventoryQty);
        } elseif ($entity instanceof InventoryItem) {
            $inventoryItemData = [];
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
            ->setInventoryItem($inventoryItemData)
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
        /** @var ArrayCollection|InventoryItem[] $inventoryItems */
        $inventoryItems = $entity->getInventoryItems();

        $inventoryItemData = [];
        /** @var InventoryItem $inventoryItem */
        foreach ($inventoryItems as $inventoryItem) {
            $inventoryItemData[] = self::getInventoryItemData($inventoryItem, $inventoryUpdateQty, $allocatedInventoryQty);
        }

        return $inventoryItemData;
    }

    /**
     * @param InventoryItem $inventoryItem
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @return array
     */
    protected static function getInventoryItemData(
        InventoryItem $inventoryItem,
        $inventoryUpdateQty,
        $allocatedInventoryQty
    ) {
        return [
            'item'          => $inventoryItem,
            'qty'           => $inventoryUpdateQty,
            'allocatedQty'  => $allocatedInventoryQty
        ];
    }
}
