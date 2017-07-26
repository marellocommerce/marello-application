<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\DataConverter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class InventoryLevelDataConverter extends AbstractTreeDataConverter
{
    /**
     * Get list of rules that should be user to convert,
     *
     * Example: array(
     *     'User Name' => 'userName', // key is frontend hint, value is backend hint
     *     'User Group' => array(     // convert data using regular expression
     *         self::FRONTEND_TO_BACKEND => array('User Group (\d+)', 'userGroup:$1'),
     *         self::BACKEND_TO_FRONTEND => array('userGroup:(\d+)', 'User Group $1'),
     *     )
     * )
     *
     * @return array
     */
    protected function getHeaderConversionRules()
    {
        return [
            'SKU'                  => 'inventoryItem:product:sku',
            'Stock Level'          => 'inventory',
            'Warehouse'            => 'warehouse:code'
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
            'inventory',
            'warehouse:label',
            'warehouse:code',
            'inventoryItem:product:desiredStockLevel',
            'inventoryItem:product:purchaseStockLevel'
        ];
    }
}
