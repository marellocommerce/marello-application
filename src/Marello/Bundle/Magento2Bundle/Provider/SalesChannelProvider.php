<?php

namespace Marello\Bundle\Magento2Bundle\Provider;

use Marello\Bundle\Magento2Bundle\Entity\Repository\WebsiteRepository;
use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;

class SalesChannelProvider
{
    /** @var array|null */
    protected $salesChannelsInfoArray;

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
     * @param bool $onlyActiveIntegration
     * @return SalesChannelInfo[]
     *
     * [
     *    'sales_channel_id' => SalesChannelInfo <SalesChannelInfo>
     * ]
     */
    public function getSalesChannelsInfoArray(
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): array {
        $this->loadSalesChannelsInfo();
        return \array_filter(
            $this->salesChannelsInfoArray,
            function (SalesChannelInfo $salesChannelInfo) use ($onlyActiveSalesChannel, $onlyActiveIntegration) {
                return $this->isApplicableSalesChannel(
                    $salesChannelInfo,
                    $onlyActiveSalesChannel,
                    $onlyActiveIntegration
                );
            });
    }

    /**
     * @param int $integrationId
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @return array
     */
    public function getSalesChannelIdsByIntegrationId(
        int $integrationId,
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): array {
        $this->loadSalesChannelsInfo();
        $filteredSalesChannels = \array_filter(
            $this->salesChannelsInfoArray,
            function (SalesChannelInfo $salesChannelInfo) use (
                $integrationId,
                $onlyActiveSalesChannel,
                $onlyActiveIntegration
            ) {
                return $this->isApplicableSalesChannel(
                        $salesChannelInfo,
                        $onlyActiveSalesChannel,
                        $onlyActiveIntegration
                    ) && $salesChannelInfo->getIntegrationChannelId() === $integrationId;
            });

        return \array_keys($filteredSalesChannels);
    }

    /**
     * @param int $salesChannelId
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @return int|null
     */
    public function getIntegrationIdBySalesChannelId(
        int $salesChannelId,
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): ?int {
        $this->loadSalesChannelsInfo();

        if (!isset($this->salesChannelsInfoArray[$salesChannelId])) {
            return null;
        }

        /** @var SalesChannelInfo $salesChannelInfo */
        $salesChannelInfo = $this->salesChannelsInfoArray[$salesChannelId];
        $isApplicable = $this->isApplicableSalesChannel(
            $salesChannelInfo,
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );

        return $isApplicable ? $salesChannelInfo->getIntegrationChannelId() : null;
    }

    public function clearCache(): void
    {
        $this->salesChannelsInfoArray = null;
    }

    protected function loadSalesChannelsInfo(): void
    {
        if (null === $this->salesChannelsInfoArray) {
            $this->salesChannelsInfoArray = $this->websiteRepository->getSalesChannelInfoArray();
        }
    }

    /**
     * @param SalesChannelInfo $salesChannelInfo
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @return bool
     */
    protected function isApplicableSalesChannel(
        SalesChannelInfo $salesChannelInfo,
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): bool
    {
        return
            ($salesChannelInfo->isSalesChannelActive() || !$onlyActiveSalesChannel) &&
            ($salesChannelInfo->isIntegrationActive() || !$onlyActiveIntegration);
    }


}
