<?php

namespace Marello\Bundle\Magento2Bundle\Provider;

use Marello\Bundle\Magento2Bundle\Entity\Repository\WebsiteRepository;
use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Model\SalesChannelsAwareInterface;

class TrackedSalesChannelProvider
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
     * @param SalesChannel $salesChannel
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @return bool
     */
    public function isTrackedSalesChannel(
        SalesChannel $salesChannel,
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): bool {
        return $this->isTrackedSalesChannelId(
            $salesChannel->getId(),
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );
    }

    /**
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @return bool
     */
    public function hasTrackedSalesChannels(
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): bool {
        $this->loadSalesChannelsInfo();
        $filteredSalesChannelInfos = \array_filter(
            $this->salesChannelsInfoArray,
            function (SalesChannelInfo $salesChannelInfo) use (
                $onlyActiveSalesChannel,
                $onlyActiveIntegration
            ) {
                return $this->isApplicableSalesChannel(
                    $salesChannelInfo,
                    $onlyActiveSalesChannel,
                    $onlyActiveIntegration
                );
            });

        return !empty($filteredSalesChannelInfos);
    }

    /**
     * @param int $salesChannelId
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @return bool
     */
    public function isTrackedSalesChannelId(
        int $salesChannelId,
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): bool {
        $this->loadSalesChannelsInfo();
        $filteredSalesChannelInfos = \array_filter(
            $this->salesChannelsInfoArray,
            function (SalesChannelInfo $salesChannelInfo) use (
                $onlyActiveSalesChannel,
                $onlyActiveIntegration
            ) {
                return $this->isApplicableSalesChannel(
                    $salesChannelInfo,
                    $onlyActiveSalesChannel,
                    $onlyActiveIntegration
                );
            });

        return \array_key_exists($salesChannelId, $filteredSalesChannelInfos);
    }

    /**
     * @param int $integrationId
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @return SalesChannelInfo[]
     */
    public function getSalesChannelInfosByIntegrationId(
        int $integrationId,
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): array {
        $this->loadSalesChannelsInfo();
        return \array_filter(
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
        $filteredSalesChannelInfos = $this->getSalesChannelInfosByIntegrationId(
            $integrationId,
            $onlyActiveSalesChannel,
            $onlyActiveIntegration
        );

        return \array_keys($filteredSalesChannelInfos);
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

    /**
     * @param SalesChannelsAwareInterface $entity
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @return bool
     */
    public function isSalesChannelAwareEntityHasTrackedSalesChannels(
        SalesChannelsAwareInterface $entity,
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): bool {
        $this->loadSalesChannelsInfo();
        $filteredSalesChannelInfos = \array_filter(
            $this->salesChannelsInfoArray,
            function (SalesChannelInfo $salesChannelInfo) use (
                $onlyActiveSalesChannel,
                $onlyActiveIntegration
            ) {
                return $this->isApplicableSalesChannel(
                        $salesChannelInfo,
                        $onlyActiveSalesChannel,
                        $onlyActiveIntegration
                    );
            });

        $hasTrackedSalesChannel = false;
        foreach ($entity->getChannels() as $salesChannel) {
            if (\array_key_exists($salesChannel->getId(), $filteredSalesChannelInfos)) {
                $hasTrackedSalesChannel = true;
                break;
            }
        }

        return $hasTrackedSalesChannel;
    }

    /**
     * @param SalesChannelsAwareInterface $entity
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @return int[]
     */
    public function getIntegrationIdsFromSalesChannelAwareEntity(
        SalesChannelsAwareInterface $entity,
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): array {
        $this->loadSalesChannelsInfo();
        $entitySalesChannelIds = $entity->getChannels()->map(function (SalesChannel $salesChannel) {
            return $salesChannel->getId();
        })->toArray();

        $filteredSalesChannelInfos = \array_filter(
            $this->salesChannelsInfoArray,
            function (SalesChannelInfo $salesChannelInfo) use (
                $onlyActiveSalesChannel,
                $onlyActiveIntegration,
                $entitySalesChannelIds
            ) {
                return $this->isApplicableSalesChannel(
                    $salesChannelInfo,
                    $onlyActiveSalesChannel,
                    $onlyActiveIntegration
                ) && \in_array($salesChannelInfo->getSalesChannelId(), $entitySalesChannelIds, true);
            });

        return \array_unique(
            \array_map(function (SalesChannelInfo $salesChannelInfo) {
                return $salesChannelInfo->getIntegrationChannelId();
            }, $filteredSalesChannelInfos)
        );
    }

    /**
     * @param bool $onlyActiveSalesChannel
     * @param bool $onlyActiveIntegration
     * @return array
     * [
     *      <currency_name_1> => [<sales_channel_id> => <sales_channel_info>, ...],
     *      ..
     * ]
     */
    public function getTrackedSalesChannelCurrenciesWithSalesChannelInfos(
        bool $onlyActiveSalesChannel = true,
        bool $onlyActiveIntegration = true
    ): array {
        $this->loadSalesChannelsInfo();
        /** @var SalesChannelInfo[] $filteredSalesChannelInfos */
        $filteredSalesChannelInfos = \array_filter(
            $this->salesChannelsInfoArray,
            function (SalesChannelInfo $salesChannelInfo) use (
                $onlyActiveSalesChannel,
                $onlyActiveIntegration
            ) {
                return $this->isApplicableSalesChannel(
                        $salesChannelInfo,
                        $onlyActiveSalesChannel,
                        $onlyActiveIntegration
                    );
            });

        $currenciesWithSChIds = [];
        foreach ($filteredSalesChannelInfos as $salesChannelInfo) {
            $currency = $salesChannelInfo->getSalesChannelCurrency();
            $salesChannelId = $salesChannelInfo->getSalesChannelId();
            if (!\array_key_exists($currency, $currenciesWithSChIds)) {
                $currenciesWithSChIds[$currency] = [];
            }

            $currenciesWithSChIds[$currency][$salesChannelId] = $salesChannelInfo;
        }

        return $currenciesWithSChIds;
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
    ): bool {
        return
            ($salesChannelInfo->isSalesChannelActive() || !$onlyActiveSalesChannel) &&
            ($salesChannelInfo->isIntegrationActive() || !$onlyActiveIntegration);
    }
}
