<?php

namespace Marello\Bundle\Magento2Bundle\Model;

class SalesChannelInfo
{
    /** @var int */
    protected $websiteId;

    /** @var int */
    protected $integrationChannelId;

    /** @var bool */
    protected $salesChannelActive;

    /** @var bool */
    protected $integrationActive;

    /**
     * @param int $websiteId
     * @param int $integrationChannelId
     * @param bool $salesChannelActive
     * @param bool $integrationActive
     */
    public function __construct(
        int $websiteId,
        int $integrationChannelId,
        bool $salesChannelActive,
        bool $integrationActive
    ) {
        $this->websiteId = $websiteId;
        $this->integrationChannelId = $integrationChannelId;
        $this->salesChannelActive = $salesChannelActive;
        $this->integrationActive = $integrationActive;
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

    /**
     * @return bool
     */
    public function isSalesChannelActive(): bool
    {
        return $this->salesChannelActive;
    }

    /**
     * @return bool
     */
    public function isIntegrationActive(): bool
    {
        return $this->integrationActive;
    }
}
