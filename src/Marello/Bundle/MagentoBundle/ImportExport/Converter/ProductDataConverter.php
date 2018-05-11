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
        $result = parent::convertToImportFormat($importedRecord, $skipNullValues);

        $dateObj = new \DateTime('now', new \DateTimeZone('UTC'));
        $date = $dateObj->format('Y-m-d H:i:s');
        $result['createdAt'] = $date;
        $result['updatedAt'] = $date;

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
