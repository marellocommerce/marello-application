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
            'SKU'            => 'inventoryItem:product:sku',
            'Warehouse Code' => 'warehouse:code',
            'Adjustment'     => 'inventory'
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
            'inventory'
        ];
    }

    /**
     * Fixed the issue with ignoring the "skipNullValues" parameter
     * @param array $exportedRecord
     * @param bool $skipNullValues
     * @return array
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $exportedRecord = parent::convertToExportFormat($exportedRecord, $skipNullValues);

        if ($skipNullValues) {
            $exportedRecord = $this->removeEmptyColumns($exportedRecord, $skipNullValues);
        }

        return $exportedRecord;
    }
}
