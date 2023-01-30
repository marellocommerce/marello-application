<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class AvailableInventoryProvider
{
    public function __construct(
        protected DoctrineHelper $doctrineHelper,
        protected AclHelper $aclHelper
    ) {
    }

    /**
     * Get available inventory for a product in a certain saleschannel
     * @param $product Product
     * @param $salesChannel SalesChannel
     * @return int
     */
    public function getAvailableInventory(Product $product, SalesChannel $salesChannel)
    {
        $balancedInventory = $this->getAvailableBalancedInventory($product, $salesChannel);

        return $balancedInventory;
    }

    /**
     * @param $product Product
     * @param $salesChannel SalesChannel
     * @return int
     */
    private function getAvailableBalancedInventory(Product $product, SalesChannel $salesChannel)
    {
        $salesChannelGroup = $salesChannel->getGroup();
        $result = $this->getBalancedInventoryLevel($product, $salesChannelGroup);

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
            ->findBySalesChannel($channelId, $productIds, $this->aclHelper);
    }

    /**
     * Get associated BalancedInventoryLevel
     * @param Product $product
     * @param SalesChannelGroup $salesChannelGroup
     * @return BalancedInventoryLevel
     */
    protected function getBalancedInventoryLevel(Product $product, SalesChannelGroup $salesChannelGroup)
    {
        return $this->doctrineHelper
            ->getEntityManagerForClass(BalancedInventoryLevel::class)
            ->getRepository(BalancedInventoryLevel::class)
            ->findExistingBalancedInventory($product, $salesChannelGroup, $this->aclHelper);
    }
}
