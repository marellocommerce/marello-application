<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class ProductDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'product_id'        => 'originId',
            'sku'               => 'sku',
            'name'              => 'name',
            'type'              => 'type',
            'price'             => 'price',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord['website:channel:id'] = $this->context->getOption('channel');

        $result = parent::convertToImportFormat($importedRecord, $skipNullValues);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }
}
