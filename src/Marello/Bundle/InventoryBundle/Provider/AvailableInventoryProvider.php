<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Symfony\Component\Translation\TranslatorInterface;

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

    /** @var TranslatorInterface $translator */
    protected $translator;

    /**
     * {@inheritdoc}
     * @param DoctrineHelper $doctrineHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        TranslatorInterface $translator
    ){
        $this->doctrineHelper = $doctrineHelper;
        $this->translator = $translator;
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
     * @param $productIdentifier
     * @param $requestedQuantities
     * @param $availableInventory
     * @return bool
     */
    public function isValidRequestedQuantity($productIdentifier, $requestedQuantities, $availableInventory)
    {
        if (!array_key_exists($productIdentifier, $requestedQuantities)) {
            return false;
        }

        return ($requestedQuantities[$productIdentifier] <= (int)$availableInventory);
    }

    /**
     * Get products by saleschannel
     * @param int $channelId
     * @param array $productIds
     * @return ProductInterface|null
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
