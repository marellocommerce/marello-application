<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class StoreDataConverter extends IntegrationAwareDataConverter
{
    public const ID_COLUMN_NAME = 'id';

    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            self::ID_COLUMN_NAME => 'originId',
            'code' => 'code',
            'name' => 'name',
            'website_id' => 'website:originId',
            'is_active' => 'isActive',
            'locale' => 'localeId',
            'base_currency_code' => 'baseCurrencyCode'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord['website:channel:id'] = $this->context->getOption('channel');

        /**
         * Populate localization field with correct localization based on formattingCode
         */
        if (!empty($importedRecord['locale'])) {
            $importedRecord['localization:formattingCode'] = $importedRecord['locale'];
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }
}
