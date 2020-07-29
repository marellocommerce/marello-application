<?php

namespace Marello\Bundle\Magento2Bundle\Transport\Rest\Iterator;

use Marello\Bundle\Magento2Bundle\Iterator\AbstractLoadeableIterator;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Marello\Bundle\Magento2Bundle\ImportExport\Converter\WebsiteDataConverter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class WebsiteIterator extends AbstractLoadeableIterator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
            return isset($websiteData[WebsiteDataConverter::ID_COLUMN_NAME]) &&
                self::ADMIN_WEBSITE_ID !== $websiteData[WebsiteDataConverter::ID_COLUMN_NAME];
        });

        return \array_map(function (array $websiteData) {
            $websiteData[WebsiteDataConverter::SALES_CHANNEL_CODE_COLUMN_ID] = null;
            if (isset($websiteData[WebsiteDataConverter::ID_COLUMN_NAME])) {
                $salesChannelId = $this->settingsBag->getSalesChannelIdByWebsiteId(
                    $websiteData[WebsiteDataConverter::ID_COLUMN_NAME]
                );
                $websiteData[WebsiteDataConverter::SALES_CHANNEL_CODE_COLUMN_ID] = $salesChannelId;
            } else {
                $this->logInvalidWebsiteDataFound($websiteData);
            }

            return $websiteData;
        }, $data);
    }

    /**
     * @param array $websiteData
     */
    protected function logInvalidWebsiteDataFound(array $websiteData): void
    {
        $this->logger->warning(
            '[Magento 2] Skipped website, because it doesn\'t contain identifier column in data.',
            [
                'identifierColumnName' => WebsiteDataConverter::ID_COLUMN_NAME,
                'data' => $websiteData
            ]
        );
    }
}
