<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class ProductTaxClassDataConverter extends IntegrationAwareDataConverter
{
    public const MAGENTO_FIELD_ID_NAME = 'class_id';
    public const MAGENTO_FIELD_TAX_TYPE_NAME = 'class_type';

    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            self::MAGENTO_FIELD_ID_NAME => 'originId',
            'class_name' => 'className'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord['taxCode:code'] = $importedRecord['class_name'];

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * {@inheritDoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }
}
