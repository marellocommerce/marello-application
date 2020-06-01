<?php

namespace Marello\Bundle\Magento2Bundle\Provider;

use Marello\Bundle\Magento2Bundle\Entity\Repository\WebsiteRepository;
use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;

class SalesChannelInfosProvider
{
    /** @var array|null */
    protected $salesChannelsInfoArray = null;

    /** @var WebsiteRepository */
    protected $websiteRepository;

    /**
     * @param WebsiteRepository $websiteRepository
     */
    public function __construct(WebsiteRepository $websiteRepository)
    {
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @param bool $onlyActiveSalesChannel
     * @return SalesChannelInfo[]
     *
     * [
     *    'sales_channel_id' => SalesChannelInfo <SalesChannelInfo>
     * ]
     */
    public function getSalesChannelsInfoArray(bool $onlyActiveSalesChannel = true): array
    {
        if (null === $this->salesChannelsInfoArray) {
            $this->salesChannelsInfoArray = $this->websiteRepository->getSalesChannelInfoArray();
        }

        if ($onlyActiveSalesChannel) {
            return \array_filter($this->salesChannelsInfoArray, function (SalesChannelInfo $salesChannelInfo) {
                return $salesChannelInfo->isIntegrationActive() && $salesChannelInfo->isSalesChannelActive();
            });
        }

        return \array_filter($this->salesChannelsInfoArray, function (SalesChannelInfo $salesChannelInfo) {
            return $salesChannelInfo->isIntegrationActive();
        });
    }

    /**
     * @param int $salesChannelId
     * @param bool $onlyActiveSalesChannel
     * @return int|null
     */
    public function getIntegrationIdBySalesChannelId(int $salesChannelId, bool $onlyActiveSalesChannel = true): ?int
    {
        if (!isset($this->salesChannelsInfoArray[$salesChannelId])) {
            return null;
        }

        /** @var SalesChannelInfo $salesChannelInfo */
        $salesChannelInfo = $this->salesChannelsInfoArray[$salesChannelId];

        if ($onlyActiveSalesChannel) {
            if ($salesChannelInfo->isSalesChannelActive() && $salesChannelInfo->isIntegrationActive()) {
                return $salesChannelInfo->getIntegrationChannelId();
            }

            return null;
        }

        return $salesChannelInfo->isIntegrationActive() ? $salesChannelInfo->getIntegrationChannelId() : null;
    }

    public function clearCache(): void
    {
        $this->salesChannelsInfoArray = [];
    }
}
