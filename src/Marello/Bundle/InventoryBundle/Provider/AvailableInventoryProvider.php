<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;

class AvailableInventoryProvider
{
    /**
     * @var DoctrineHelper
     */
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
        $balancedInventory = $this->getAvailableBalancedInventory($product, $salesChannel);
        $externalInventory = $this->getAvailableExternalInventory($product);

        return $balancedInventory + $externalInventory;
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
     * @param $product Product
     * @return int
     */
    private function getAvailableExternalInventory(Product $product)
    {
        $inventory = 0;
        foreach ($product->getInventoryItems() as $inventoryItem) {
            foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                $warehouseType = $inventoryLevel->getWarehouse()->getWarehouseType()->getName();
                if ($warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                    $inventory += $inventoryLevel->getVirtualInventoryQty();
                }
            }
        }

        return $inventory;
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
            ->findExistingBalancedInventory($product, $salesChannelGroup);
    }
}
