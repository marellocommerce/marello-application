<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryItemAwareInterface;

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
        $inventoryItemData = null;
        if ($entity instanceof InventoryItemAwareInterface) {
            $inventoryItemData = self::getInventoryItemDataFromInterface($entity, $inventoryUpdateQty, $allocatedInventoryQty);
        } elseif ($entity instanceof InventoryItem) {
            $inventoryItemData = self::getInventoryItemData($entity, $inventoryUpdateQty, $allocatedInventoryQty);
        }

        if (!$inventoryItemData) {
            return null;
        }

        $context = new InventoryUpdateContext();
        $context
            ->setStock($inventoryUpdateQty)
            ->setAllocatedStock($$allocatedInventoryQty)
            ->setChangeTrigger($trigger)
            ->setItems($inventoryItemData)
            ->setRelatedEntity($relatedEntity)
        ;

        return $context;
    }

    /**
     * @param InventoryItemAwareInterface $entity
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @return array
     */
    protected function getInventoryItemDataFromInterface(
        InventoryItemAwareInterface $entity,
        $inventoryUpdateQty,
        $allocatedInventoryQty
    ) {
        /** @var ArrayCollection|InventoryItem[] $inventoryItems */
        $inventoryItems = $entity->getInventoryItems();

        $inventoryItemData = [];
        /** @var InventoryItem $inventoryItem */
        foreach ($inventoryItems as $inventoryItem) {
            $inventoryItemData[] = $this->getInventoryItemData($inventoryItem, $inventoryUpdateQty, $allocatedInventoryQty);
        }

        return $inventoryItemData;
    }

    /**
     * @param InventoryItem $inventoryItem
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @return array
     */
    protected function getInventoryItemData(
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