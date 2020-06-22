<?php

namespace Marello\Bundle\Magento2Bundle\Model;

class SalesChannelInfo
{
    /** @var int */
    protected $salesChannelId;

    /** @var int */
    protected $websiteId;

    /** @var int */
    protected $integrationChannelId;

    /** @var bool */
    protected $salesChannelActive;

    /** @var bool */
    protected $integrationActive;

    /** @var string */
    protected $salesChannelCurrency;

    /**
     * @param int $salesChannelId
     * @param int $websiteId
     * @param int $integrationChannelId
     * @param bool $salesChannelActive
     * @param bool $integrationActive
     * @param string $salesChannelCurrency
     */
    public function __construct(
        int $salesChannelId,
        int $websiteId,
        int $integrationChannelId,
        bool $salesChannelActive,
        bool $integrationActive,
        string $salesChannelCurrency
    ) {
        $this->salesChannelId = $salesChannelId;
        $this->websiteId = $websiteId;
        $this->integrationChannelId = $integrationChannelId;
        $this->salesChannelActive = $salesChannelActive;
        $this->integrationActive = $integrationActive;
        $this->salesChannelCurrency = $salesChannelCurrency;
    }

    /**
     * @return int
     */
    public function getSalesChannelId(): int
    {
        return $this->salesChannelId;
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

    /**
     * @return string
     */
    public function getSalesChannelCurrency(): string
    {
        return $this->salesChannelCurrency;
    }
}
