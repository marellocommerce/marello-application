<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class WebsiteDataConverter extends IntegrationAwareDataConverter
{
    /** @var string */
    public const SALES_CHANNEL_CODE_COLUMN_NAME = 'salesChannelGroupCode';

    private const NAME_MAX_LENGTH = 255;
    private const CODE_MAX_LENGTH = 32;
    private const ELLIPSIS = '...';
    
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'id' => 'originId',
            'code' => 'code',
            'name' => 'name',
            self::SALES_CHANNEL_CODE_COLUMN_NAME => 'salesChannel:code'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (!empty($importedRecord['name']) && mb_strlen($importedRecord['name']) > self::NAME_MAX_LENGTH) {
            $importedRecord['name'] = $this->cutFieldToLength($importedRecord['name'], self::NAME_MAX_LENGTH);
        }

        if (!empty($importedRecord['code']) && mb_strlen($importedRecord['code']) > self::CODE_MAX_LENGTH) {
            $importedRecord['code'] = $this->cutFieldToLength($importedRecord['code'], self::CODE_MAX_LENGTH);
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * Cuts field value to max length and add ellipsis
     *
     * @param string $fieldValue
     * @param int $maxLength
     *
     * @return string
     */
    private function cutFieldToLength($fieldValue, $maxLength): string
    {
        $ellipsisLength = \mb_strlen(self::ELLIPSIS);
        $fieldValue = sprintf(
            '%s%s',
            \mb_strcut($fieldValue, 0, $maxLength - $ellipsisLength),
            self::ELLIPSIS
        );

        return $fieldValue;
    }
}
