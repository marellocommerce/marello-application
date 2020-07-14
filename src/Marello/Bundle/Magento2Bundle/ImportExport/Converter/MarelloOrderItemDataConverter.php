<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class MarelloOrderItemDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'productName' => 'name',
            'productSku' => 'sku',
            'quantity' => 'qty_ordered',
            'price' => 'price',
            'originalPriceInclTax' => 'base_price_incl_tax',
            'originalPriceExlTax' => 'base_original_price',
            'tax' => 'tax_amount',
            'taxPercent' => 'tax_percent',
            'discountPercent' => 'discount_percent',
            'discountAmount' => 'discount_amount',
            'rowTotalInclTax' => 'row_total_incl_tax',
            'rowTotalExclTax' => 'row_total',
            'subtotal' => 'row_total',
            'totalTax' => 'tax_amount',
            'grandTotal' => 'row_total_incl_tax'
        ];
    }

    /**
     * @return array|void
     */
    protected function getBackendHeader()
    {
        throw new RuntimeException('Normalization is not implemented!');
    }
}
