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
        $data = \array_filter($this->data, function (array $websiteData) {
            return isset($websiteData['id']) && self::ADMIN_WEBSITE_ID !== $websiteData['id'];
        });

        return \array_map(function (array $websiteData) {
            $websiteData[WebsiteDataConverter::SALES_CHANNEL_CODE_COLUMN_NAME] = null;
            if (isset($websiteData['code'])) {
                $websiteData[WebsiteDataConverter::SALES_CHANNEL_CODE_COLUMN_NAME] = $this->settingsBag->getSalesChannelCodeByWebsiteCode(
                    $websiteData['code']
                );
            }

            return $websiteData;
        }, $data);
    }
}
