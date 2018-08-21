<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;

class AvailableInventoryProvider
{
    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /**
     * {@inheritdoc}
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Get available inventory for a product in a certain saleschannel
     * @param $product Product
     * @param $salesChannel SalesChannel
     * @return int
     */
    public function getAvailableInventory(Product $product, SalesChannel $salesChannel)
    {
        $salesChannelGroup = $salesChannel->getGroup();
        $result = $this->getVirtualInventoryLevel($product, $salesChannelGroup);

        return ($result) ? $result->getInventoryQty() : 0;
    }

    /**
     * Get products by saleschannel
     * @param int $channelId
     * @param array $productIds
     * @return ProductInterface[]|null
     */
    public function getProducts($channelId, $productIds)
    {
        return $this->doctrineHelper
            ->getEntityManagerForClass(Product::class)
            ->getRepository(Product::class)
            ->findBySalesChannel($channelId, $productIds);
    }

    /**
     * Get associated VirtualInventoryLevel
     * @param Product $product
     * @param SalesChannelGroup $salesChannelGroup
     * @return VirtualInventoryLevel
     */
    protected function getVirtualInventoryLevel(Product $product, SalesChannelGroup $salesChannelGroup)
    {
        return $this->doctrineHelper
            ->getEntityManagerForClass(VirtualInventoryLevel::class)
            ->getRepository(VirtualInventoryLevel::class)
            ->findExistingVirtualInventory($product, $salesChannelGroup);
    }
}
