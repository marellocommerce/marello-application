<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Translator\SimpleProduct;

use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Repository\BalancedInventoryRepository;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\Magento2Bundle\ImportExport\Translator\TranslatorInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class ProductTranslator implements TranslatorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var BalancedInventoryRepository */
    protected $balancedInventoryRepository;

    /**
     * @param BalancedInventoryRepository $balancedInventoryRepository
     */
    public function setBalancedInventoryRepository(BalancedInventoryRepository $balancedInventoryRepository)
    {
        $this->balancedInventoryRepository = $balancedInventoryRepository;
    }

    /**
     * @param Product $product
     * @param int $channelId
     * @return Website[]
     */
    protected function getWebsitesProductAttachedTo(Product $product, int $channelId): array
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

            if ($website->getChannelId() === $channelId) {
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
    protected function getBalancedInventoryLevel(Product $product, Website $website): ?BalancedInventoryLevel
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
}
