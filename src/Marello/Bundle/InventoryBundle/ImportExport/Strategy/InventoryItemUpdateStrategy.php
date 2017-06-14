<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Strategy;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class InventoryItemUpdateStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @param object|InventoryItem $entity
     * @param bool                 $isFullData
     * @param bool                 $isPersistNew
     * @param null                 $itemData
     * @param array                $searchContext
     * @param bool                 $entityIsRelation
     *
     * @return null
     */
    protected function processEntity(
        $entity,
        $isFullData = false,
        $isPersistNew = false,
        $itemData = null,
        array $searchContext = [],
        $entityIsRelation = false
    ) {
        $item = $this->findInventoryItem($entity);

        if (!$item) {
            return null;
        }

        $this->handleInventoryUpdate($item, $entity->getStock(), null, null);

        return $item;
    }

    /**
     * handle the inventory update for items which have been shipped
     * @param InventoryItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param $entity
     */
    protected function handleInventoryUpdate($item, $inventoryUpdateQty, $allocatedInventoryQty, $entity)
    {
//        $inventoryItems[] = $item;
//        $inventoryItemData = [];
//        foreach ($inventoryItems as $inventoryItem) {
//            $inventoryItemData[] = [
//                'item'          => $inventoryItem,
//                'qty'           => $inventoryUpdateQty,
//                'allocatedQty'  => $allocatedInventoryQty
//            ];
//        }
//
//        $data = [
//            'stock'             => $inventoryUpdateQty,
//            'allocatedStock'    => $allocatedInventoryQty,
//            'trigger'           => 'import',
//            'items'             => $inventoryItemData,
//            'relatedEntity'     => $entity
//        ];
//
//        $context = InventoryUpdateContext::createUpdateContext($data);

        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'import',
            $entity
        );

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );
    }

    /**
     * @param InventoryItem $entity
     *
     * @return null|InventoryItem
     */
    protected function findInventoryItem($entity)
    {
        $product = $this->databaseHelper->findOneBy(Product::class, ['sku' => $entity->getProduct()->getSku()]);

        if (!$product) {
            return null;
        }

        return $this->databaseHelper->findOneBy(InventoryItem::class, ['product' => $product]);
    }
    
    /**
     * Increment context counters.
     *
     * @param $entity
     */
    protected function updateContextCounters($entity)
    {
        $identifier = $this->databaseHelper->getIdentifier($entity);
        if ($identifier) {
            $this->context->incrementUpdateCount();
        } else {
            $this->context->incrementAddCount();
        }
    }
}
