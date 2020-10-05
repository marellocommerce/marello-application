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
            'base_price_incl_tax' => 'purchasePriceIncl',
            'base_original_price' => 'originalPriceInclTax',
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
        // we should convert some of the configurable data to a simple as this is missing on an Order item with simple product
        if (!empty($importedRecord['parent_item'])) {
            $importedRecord['price'] = $importedRecord['parent_item']['price'];
            $importedRecord['base_price_incl_tax'] = $importedRecord['parent_item']['base_price_incl_tax'];
            $importedRecord['base_original_price'] = $importedRecord['parent_item']['base_original_price'];
            $importedRecord['tax_amount'] = $importedRecord['parent_item']['tax_amount'];
            $importedRecord['tax_percent'] = $importedRecord['parent_item']['tax_percent'];
            $importedRecord['discount_percent'] = $importedRecord['parent_item']['discount_percent'];
            $importedRecord['discount_amount'] = $importedRecord['parent_item']['discount_amount'];
            $importedRecord['row_total_incl_tax'] = $importedRecord['parent_item']['row_total_incl_tax'];
            $importedRecord['base_row_total_incl_tax'] = $importedRecord['parent_item']['base_row_total_incl_tax'];
            $importedRecord['row_total'] = $importedRecord['parent_item']['row_total'];
        }

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
