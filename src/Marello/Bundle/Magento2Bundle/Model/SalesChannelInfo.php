<?php

namespace Marello\Bundle\Magento2Bundle\Model;

class SalesChannelInfo
{
    /** @var int */
    protected $websiteId;

    /** @var int */
    protected $integrationChannelId;

    /**
     * @param int $websiteId
     * @param int $integrationChannelId
     */
    public function __construct(int $websiteId, int $integrationChannelId)
    {
        $this->websiteId = $websiteId;
        $this->integrationChannelId = $integrationChannelId;
    }

    /**
     * @return int
     */
    public function getWebsiteId(): int
    {
        return $this->websiteId;
    }

    /**
     * @return int
     */
    public function getIntegrationChannelId(): int
    {
        return $this->integrationChannelId;
    }
}
