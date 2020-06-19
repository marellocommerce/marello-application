<?php

namespace Marello\Bundle\Magento2Bundle\Model;

use Symfony\Component\HttpFoundation\ParameterBag;

class Magento2TransportSettings extends ParameterBag
{
    public const API_URL_KEY = 'api_url';
    public const API_TOKEN_KEY = 'api_token';
    public const SYNC_RANGE_KEY = 'sync_range';
    public const START_SYNC_DATE_KEY = 'start_sync_date';
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
     * @return \DateInterval|null
     */
    public function getSyncRange(): ?\DateInterval
    {
        return $this->get(self::SYNC_RANGE_KEY);
    }

    /**
     * @return \DateTime|null
     */
    public function getSyncStartDate(): ?\DateTime
    {
        return $this->get(self::START_SYNC_DATE_KEY);
    }

    /**
     * @return \DateTime|null
     */
    public function getInitialSyncStartDate(): ?\DateTime
    {
        return $this->get(self::INITIAL_SYNC_START_DATE_KEY);
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
     * @param string $websiteCode
     * @return string|null
     */
    public function getSalesChannelCodeByWebsiteCode(string $websiteCode): ?string
    {
        $websiteToSalesChannelMapping = $this->get(self::WEBSITE_TO_SALES_CHANNEL_MAPPING_KEY, []);
        foreach ($websiteToSalesChannelMapping as $websiteToSalesChannelMappingItem) {
            if ($websiteCode === $websiteToSalesChannelMappingItem['website_code']) {
                return $websiteToSalesChannelMappingItem['sales_chanel_code'];
            }
        }

        return null;
    }
}
