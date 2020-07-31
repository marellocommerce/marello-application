<?php

namespace Marello\Bundle\Magento2Bundle\Model;

/**
 *
 */
class WebsiteToSalesChannelMapItem
{
    /** @var int|null */
    protected $websiteOriginId;

    /** @var string|null */
    protected $websiteName;

    /** @var int|null */
    protected $salesChannelId;

    /** @var string|null */
    protected $salesChannelName;

    /**
     * @param int $websiteOriginId
     * @param string $websiteName
     * @param int $salesChannelId
     * @param string $salesChannelName
     */
    public function __construct(
        int $websiteOriginId = null,
        string $websiteName = null,
        int $salesChannelId = null,
        string $salesChannelName = null
    ) {
        $this->websiteOriginId = $websiteOriginId;
        $this->websiteName = $websiteName;
        $this->salesChannelId = $salesChannelId;
        $this->salesChannelName = $salesChannelName;
    }

    /**
     * @return callable
     */
    public static function createFromCallable(): callable
    {
        return static::class . '::createFrom';
    }

    /**
     * @param array $data
     * @return static
     */
    public static function createFrom(array $data): self
    {
        return new static(
            $data['websiteOriginId'] ?? null,
            $data['websiteName'] ?? null,
            $data['salesChannelId'] ?? null,
            $data['salesChannelName'] ?? null
        );
    }

    /**
     * @return int|null
     */
    public function getWebsiteOriginId(): ?int
    {
        return $this->websiteOriginId;
    }

    /**
     * @param int|null $websiteOriginId
     * @return $this
     */
    public function setWebsiteOriginId(int $websiteOriginId = null): self
    {
        $this->websiteOriginId = $websiteOriginId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWebsiteName(): ?string
    {
        return $this->websiteName;
    }

    /**
     * @param string|null $websiteName
     * @return $this
     */
    public function setWebsiteName(string $websiteName = null): self
    {
        $this->websiteName = $websiteName;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSalesChannelId(): ?int
    {
        return $this->salesChannelId;
    }

    /**
     * @param int|null $salesChannelId
     * @return $this
     */
    public function setSalesChannelId(int $salesChannelId = null): self
    {
        $this->salesChannelId = $salesChannelId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSalesChannelName(): ?string
    {
        return $this->salesChannelName;
    }

    /**
     * @param string|null $salesChannelName
     * @return $this
     */
    public function setSalesChannelName(string $salesChannelName = null): self
    {
        $this->salesChannelName = $salesChannelName;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'websiteOriginId' => $this->getWebsiteOriginId(),
            'websiteName' => $this->getWebsiteName(),
            'salesChannelId' => $this->getSalesChannelId(),
            'salesChannelName' => $this->getSalesChannelName()
        ];
    }
}
