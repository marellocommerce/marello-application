<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\Strategy;

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
        file_put_contents(
            '/Users/hotlander/Development/marello-application-dev/app/logs/inventory-import.log',
            print_r($itemData, true) . "\r\n",
            FILE_APPEND
        );
        $sku = array_shift($itemData);
        $item = $this->findProductBySku($sku);

        if (!$item) {
            return null;
        }

        $this->handleInventoryUpdate($item, $entity->getStock(), null, null);

        return $item;
    }

    protected function findProductBySku($sku)
    {
        $product = $this->databaseHelper->findOneBy(Product::class, ['sku' => $sku]);

        if (!$product) {
            $errorMessages = [$this->translator->trans(
                'oro.importexport.import.errors.not_found_entity',
                ['%entity_name%' => Product::class]
            )];
            $this->strategyHelper->addValidationErrors($errorMessages, $this->context);
        }
    }
    /**
     * handle the inventory update for items which have been shipped
     * @param Product $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param $entity
     */
    protected function handleInventoryUpdate($item, $inventoryUpdateQty, $allocatedInventoryQty, $entity)
    {
        $inventoryItems = $item->getInventoryItems();
        $inventoryItemData = [];
        foreach ($inventoryItems as $inventoryItem) {
            $inventoryItemData[] = [
                'item'          => $inventoryItem,
                'qty'           => $inventoryUpdateQty,
                'allocatedQty'  => $allocatedInventoryQty
            ];
        }

        $data = [
            'stock'             => $inventoryUpdateQty,
            'allocatedStock'    => $allocatedInventoryQty,
            'trigger'           => 'import',
            'items'             => $inventoryItemData,
            'relatedEntity'     => $entity
        ];

        $context = InventoryUpdateContext::createUpdateContext($data);
        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );
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
