<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\ProductBundle\Entity\Product;

class ProductChangesByWebsiteDTO
{
    /** @var int */
    protected $integrationChannelId;

    /** @var int */
    protected $websiteId;

    /** @var array */
    protected $updatedProductIds = [];

    /**
     * @param int $integrationChannelId
     * @param int $websiteId
     */
    public function __construct(int $integrationChannelId, int $websiteId)
    {
        $this->integrationChannelId = $integrationChannelId;
        $this->websiteId = $websiteId;
    }

    /**
     * @param Product $product
     */
    public function addUpdatedProduct(Product $product)
    {
        if (null === $product->getId()) {
            return;
        }

        $this->updatedProductIds[$product->getId()] = $product->getId();
    }

    /**
     * @return array
     */
    public function getUpdatedProductIds(): array
    {
        return $this->updatedProductIds;
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
    public function getIntegrationId(): int
    {
        return $this->integrationChannelId;
    }
}
