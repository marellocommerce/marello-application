<?php

namespace Marello\Bundle\Magento2Bundle\Model;

use Marello\Bundle\Magento2Bundle\DTO\WebsiteToSalesChannelMappingItemDTO;
use Symfony\Component\HttpFoundation\ParameterBag;

class Magento2TransportSettings extends ParameterBag
{
    public const API_URL_KEY = 'api_url';
    public const API_TOKEN_KEY = 'api_token';
    public const SYNC_START_DATE_KEY = 'sync_start_date';
    public const INITIAL_SYNC_START_DATE_KEY = 'initial_sync_start_date';
    public const WEBSITE_TO_SALES_CHANNEL_MAPPING_KEY = 'website_to_sales_channel_mapping';
    public const DELETE_REMOTE_DATA_ON_DEACTIVATION_KEY = 'delete_remote_data_on_deactivation';
    public const DELETE_REMOTE_DATA_ON_DELETION_KEY = 'delete_remote_data_on_deletion';

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->get(self::API_URL_KEY);
    }

    /**
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->get(self::API_TOKEN_KEY);
    }

    /**
     * @param bool $clone
     * @return \DateTime
     */
    public function getSyncStartDate(bool $clone = true): \DateTime
    {
        if ($clone) {
            return clone $this->get(self::SYNC_START_DATE_KEY);
        }

        return $this->get(self::SYNC_START_DATE_KEY);
    }

    /**
     * @param bool $clone
     * @return \DateTime
     * @throws \Exception
     */
    public function getInitialSyncStartDate(bool $clone = true): \DateTime
    {
        $initialSyncStartDate = $this->get(
            self::INITIAL_SYNC_START_DATE_KEY,
            new \DateTime('2007-01-01', new \DateTimeZone('UTC'))
        );

        return $clone ? clone $initialSyncStartDate : $initialSyncStartDate;
    }

    /**
     * @return bool
     */
    public function isDeleteRemoteDataOnDeactivation(): bool
    {
        return (bool) $this->get(self::DELETE_REMOTE_DATA_ON_DEACTIVATION_KEY);
    }

    /**
     * @return bool
     */
    public function isDeleteRemoteDataOnDeletion(): bool
    {
        return (bool) $this->get(self::DELETE_REMOTE_DATA_ON_DELETION_KEY);
    }

    /**
     * @param int $websiteId
     * @return int|null
     */
    public function getSalesChannelIdByWebsiteOriginId(int $websiteId): ?int
    {
        $websiteToSalesChannelMapping = $this->get(self::WEBSITE_TO_SALES_CHANNEL_MAPPING_KEY, []);
        foreach ($websiteToSalesChannelMapping as $websiteToSalesChannelMappingItem) {
            $mappingItem = new WebsiteToSalesChannelMappingItemDTO($websiteToSalesChannelMappingItem);
            if ($mappingItem->getWebsiteOriginId() === $websiteId) {
                return $mappingItem->getSalesChannelId();
            }
        }

        return null;
    }

    /**
     * @param int[] $websiteOriginIds
     * @return array
     */
    public function getMappingItemArrayContainWebsiteOriginId(array $websiteOriginIds): array
    {
        $filteredMappingItemArray = [];
        $websiteToSalesChannelMapping = $this->get(self::WEBSITE_TO_SALES_CHANNEL_MAPPING_KEY, []);
        foreach ($websiteToSalesChannelMapping as $websiteToSalesChannelMappingItem) {
            $mappingItem = new WebsiteToSalesChannelMappingItemDTO($websiteToSalesChannelMappingItem);
            if (\in_array($mappingItem->getWebsiteOriginId(), $websiteOriginIds, true)) {
                $filteredMappingItemArray[] = $mappingItem->getData();
            }
        }

        return $filteredMappingItemArray;
    }
}
