<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

class WebsiteToSalesChannelMappingItemDTO implements \JsonSerializable
{
    /** @var string[] */
    public const REQUIRED_KEYS = [
        'originWebsiteId',
        'websiteName',
        'salesChannelId',
        'salesChannelName'
    ];

    /**
     * @var array
     */
    protected $originData = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $notExistedKeys = \array_diff_key(\array_flip(self::REQUIRED_KEYS), $data);
        if (!empty($notExistedKeys)) {
            throw new \LogicException(
                'The website to sales channel mapping item must contains all required keys.',
                [
                    'missedKeys' => $notExistedKeys,
                    'originalData' => $data
                ]
            );
        }

        $this->originData = $data;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getOriginWebsiteId(): int
    {
        return $this->data['originWebsiteId'];
    }

    /**
     * @return string
     */
    public function getWebsiteName(): string
    {
        return $this->data['websiteName'];
    }

    /**
     * @param string $websiteName
     */
    public function setWebsiteName(string $websiteName): void
    {
        $this->data['websiteName'] = $websiteName;
    }

    /**
     * @return int
     */
    public function getSalesChannelId(): int
    {
        return $this->data['salesChannelId'];
    }

    /**
     * @return string
     */
    public function getSalesChannelName(): string
    {
        return $this->data['salesChannelName'];
    }

    /**
     * @param string $salesChannelName
     */
    public function setSalesChannelName(string $salesChannelName): void
    {
        $this->data['salesChannelName'] = $salesChannelName;
    }

    /**
     * @return array
     */
    public function getOriginData(): array
    {
        return $this->originData;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->data;
    }
}
