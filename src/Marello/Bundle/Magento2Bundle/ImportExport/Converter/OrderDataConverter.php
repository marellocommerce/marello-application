<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class OrderDataConverter extends IntegrationAwareDataConverter
{
    public const ID_COLUMN_NAME = 'id';
    public const SALES_CHANNEL_CODE_COLUMN_ID = 'salesChannelId';
    public const UPDATED_AT_COLUMN_NAME = 'updated_at';
    public const UPDATED_AT_COLUMN_FORMAT = 'Y-m-d H:i:s';

    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            self::ID_COLUMN_NAME => 'originId',
            self::SALES_CHANNEL_CODE_COLUMN_ID => 'salesChannel:id'
        ];
    }
}
