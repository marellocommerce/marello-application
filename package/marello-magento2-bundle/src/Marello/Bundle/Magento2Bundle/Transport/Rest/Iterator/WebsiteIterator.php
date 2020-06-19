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
            return isset($websiteData['id']) && self::ADMIN_WEBSITE_ID !== $websiteData['id'];
        });

        return \array_map(function (array $websiteData) {
            $websiteData[WebsiteDataConverter::SALES_CHANNEL_CODE_COLUMN_NAME] = null;
            if (isset($websiteData['code'])) {
                $salesChannelCode = $this->settingsBag->getSalesChannelCodeByWebsiteCode(
                    $websiteData['code']
                );
                $websiteData[WebsiteDataConverter::SALES_CHANNEL_CODE_COLUMN_NAME] = $salesChannelCode;
            } else {
                $this->logNoSalesChannelForWebsiteFound($websiteData['code']);
            }

            return $websiteData;
        }, $data);
    }

    /**
     * @param string $code
     */
    protected function logNoSalesChannelForWebsiteFound(string $code): void
    {
        $this->logger->warning(
            sprintf(
                '[Magento 2] Website with code "%s" has no attached Sales Channel.',
                $code
            )
        );
    }
}
