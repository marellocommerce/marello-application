<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\Iterator\AbstractLoadeableIterator;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Marello\Bundle\Magento2Bundle\ImportExport\Converter\WebsiteDataConverter;

class WebsiteIterator extends AbstractLoadeableIterator
{
    private const ADMIN_WEBSITE_ID = 0;

    /**
     * @var Magento2TransportSettings
     */
    protected $settingsBag;

    /**
     * @param array $data
     * @param Magento2TransportSettings $settingsBag
     */
    public function __construct(array $data, Magento2TransportSettings $settingsBag)
    {
        $this->data = $data;
        $this->settingsBag = $settingsBag;
    }

    /**
     * {@inheritDoc}
     */
    protected function getData(): array
    {
        $websiteData = [];
        foreach ($this->data as $websiteDataItem) {
            $websiteId = $websiteDataItem[WebsiteDataConverter::ID_COLUMN_NAME] ?? null;
            if (self::ADMIN_WEBSITE_ID === $websiteId) {
                continue;
            }

            $websiteDataItem[WebsiteDataConverter::SALES_CHANNEL_CODE_COLUMN_ID] = null;
            if (null !== $websiteId) {
                $salesChannelId = $this->settingsBag->getSalesChannelIdByOriginWebsiteId(
                    $websiteDataItem[WebsiteDataConverter::ID_COLUMN_NAME]
                );
                $websiteDataItem[WebsiteDataConverter::SALES_CHANNEL_CODE_COLUMN_ID] = $salesChannelId;
            }

            $websiteData[] = $websiteDataItem;
        }

        return $websiteData;
    }
}
