<?php

namespace Marello\Bundle\InventoryBundle\ImportExport\DataConverter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class InventoryItemDataConverter extends AbstractTreeDataConverter
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
     *              "header"="Level"
     * @return array
     */
    protected function getHeaderConversionRules()
    {
        return [
            'SKU'                  => 'product:sku',
            'Desired Stock Level'  => 'product:desiredStockLevel',
            'Purchase Stock Level' => 'product:purchaseStockLevel',
            'Stock Level'          => 'currentLevel:inventory',
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
            'product:sku',
            'product:desiredStockLevel',
            'product:purchaseStockLevel',
            'currentLevel:inventory',
            'inventory'
        ];
    }
}
