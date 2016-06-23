<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Strategy;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

class InventoryItemUpdateStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @param object|InventoryItem $entity
     * @param bool                 $isFullData
     * @param bool                 $isPersistNew
     * @param null                 $itemData
     * @param array                $searchContext
     *
     * @return null
     */
    protected function processEntity(
        $entity,
        $isFullData = false,
        $isPersistNew = false,
        $itemData = null,
        array $searchContext = []
    ) {
        $item = $this->findInventoryItem($entity);

        if (!$item) {
            return null;
        }

        $item->adjustStockLevels('import', $entity->getStock());

        return $item;
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
