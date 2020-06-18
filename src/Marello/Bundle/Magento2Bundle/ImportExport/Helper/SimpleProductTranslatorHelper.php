<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Helper;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Repository\BalancedInventoryRepository;
use Marello\Bundle\Magento2Bundle\Entity\ProductTaxClass;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\ProductBundle\Entity\Product;

/**
 * Contains generic methods that used by simple product translators
 */
class SimpleProductTranslatorHelper
{
    /** @var BalancedInventoryRepository */
    protected $balancedInventoryRepository;

    /**
     * @param BalancedInventoryRepository $balancedInventoryRepository
     */
    public function __construct(BalancedInventoryRepository $balancedInventoryRepository)
    {
        $this->balancedInventoryRepository = $balancedInventoryRepository;
    }

    /**
     * @param Product $product
     * @param int $integrationId
     * @return Website[]
     */
    public function getWebsitesProductAttachedTo(Product $product, int $integrationId): array
    {
        $websites = [];
        $salesChannels = $product->getChannels();
        foreach ($salesChannels as $salesChannel) {
            if (false === $salesChannel->isActive()) {
                continue;
            }

            $website = $salesChannel->getMagento2Websites()->first();
            if (!$website instanceof Website) {
                continue;
            }

            if ($website->getChannelId() === $integrationId) {
                $websites[$website->getId()] = $website;
            }
        }

        return $websites;
    }

    /**
     * @param Product $product
     * @param Website $website
     * @return BalancedInventoryLevel|null
     */
    public function getBalancedInventoryLevel(Product $product, Website $website): ?BalancedInventoryLevel
    {
        $salesChannelGroup = $website->getSalesChannel()->getGroup();
        if (null === $salesChannelGroup) {
            return null;
        }

        return $this->balancedInventoryRepository->findExistingBalancedInventory(
            $product,
            $salesChannelGroup
        );
    }

    /**
     * @param Product $product
     * @param int $integrationId
     * @return ProductTaxClass
     */
    public function getMagentoProductTaxClass(Product $product, int $integrationId): ?ProductTaxClass
    {
        if (null === $product->getTaxCode()) {
            return null;
        }

        /** @var Collection $productClasses */
        $productClasses = $product->getTaxCode()->getMagento2ProductTaxClasses();
        $targetTaxClasses = $productClasses->filter(function (ProductTaxClass $productTaxClass) use ($integrationId) {
                return $productTaxClass->getChannelId() === $integrationId;
        });

        return $targetTaxClasses->first();
    }
}
