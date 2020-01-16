<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\DataConverter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class InventoryLevelImportDataConverter extends AbstractTreeDataConverter
{
    /**
     * @return array
     */
    protected function getHeaderConversionRules()
    {
        return [
            'SKU'             => 'inventoryItem:product:sku',
            'Warehouse Code'  => 'warehouse:code',
            'Adjustment'      => 'inventory',
            'Batch Number'    => 'inventoryBatches:0:batchNumber',
            'Purchase Price'  => 'inventoryBatches:0:purchasePrice',
            'Expiration Date' => 'inventoryBatches:0:expirationDate',
        ];
    }

    /**
     * Get maximum backend header for current entity
     *
     * @return array
     */
    protected function getBackendHeader()
    {
        return [
            'inventoryItem:product:sku',
            'warehouse:code',
            'inventory',
            'inventoryBatches:0:batchNumber',
            'inventoryBatches:0:purchasePrice',
            'inventoryBatches:0:expirationDate',
        ];
    }
}
