<?php

namespace Marello\Bundle\Magento2Bundle\Model;

use Symfony\Component\HttpFoundation\ParameterBag;

class Magento2TransportSettings extends ParameterBag
{
    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->get('api_url');
    }

    /**
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->get('api_token');
    }

    /**
     * @param string $websiteCode
     * @return string|null
     */
    public function getSalesChannelCodeByWebsiteCode(string $websiteCode): ?string
    {
        /**
         * @todo Use config object
         */
        $websiteToSalesChannelMapping = $this->get('website_to_sales_channel_mapping', []);
        foreach ($websiteToSalesChannelMapping as $websiteToSalesChannelMappingItem) {
            if ($websiteCode === $websiteToSalesChannelMappingItem['website_code']) {
                return $websiteToSalesChannelMappingItem['sales_chanel_code'];
            }
        }

        return null;
    }
}
