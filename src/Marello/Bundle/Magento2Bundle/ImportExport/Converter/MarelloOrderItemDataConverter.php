<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class MarelloOrderItemDataConverter extends IntegrationAwareDataConverter
{
    const PRODUCT_SKU_COLUMN_NAME = 'sku';

    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'name' => 'productName',
            self::PRODUCT_SKU_COLUMN_NAME => 'productSku',
            'qty_ordered' => 'quantity',
            'price' => 'price',
            'base_price_incl_tax' => 'originalPriceInclTax',
            'base_original_price' => 'originalPriceExclTax',
            'tax_amount' => 'tax',
            'tax_percent' => 'taxPercent',
            'discount_percent' => 'discountPercent',
            'discount_amount' => 'discountAmount',
            'row_total_incl_tax' => 'rowTotalInclTax',
            'row_total' => 'rowTotalExclTax'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord['subtotal'] = $importedRecord['row_total'] ?? null;
        $importedRecord['totalTax'] = $importedRecord['tax_amount'] ?? null;
        $importedRecord['grandTotal'] = $importedRecord['row_total_incl_tax'] ?? null;

        if (!empty($importedRecord[self::PRODUCT_SKU_COLUMN_NAME])) {
            $importedRecord['product:sku'] = $importedRecord[self::PRODUCT_SKU_COLUMN_NAME];
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * @return array|void
     */
    protected function getBackendHeader()
    {
        throw new RuntimeException('Normalization is not implemented!');
    }
}
