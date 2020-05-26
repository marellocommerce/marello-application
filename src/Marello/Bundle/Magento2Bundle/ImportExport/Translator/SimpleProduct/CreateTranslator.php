<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Translator\SimpleProduct;

use Marello\Bundle\Magento2Bundle\DTO\ProductSimpleCreateDTO;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\Magento2Bundle\ImportExport\Translator\TranslatorInterface;
use Marello\Bundle\ProductBundle\Entity\Product;

class CreateTranslator implements TranslatorInterface
{
    /**
     * @param Product $entity
     * @param array $context
     * @return ProductSimpleCreateDTO|null
     */
    public function translate($entity, array $context = [])
    {
        /**
         * @todo Throw exception in case when input data doesn't fix requirements
         */
        if (!$entity instanceof Product || empty($context['channel'])) {
            return null;
        }

        $sku = $entity->getSku();
        $name = $entity->getDefaultName();
        $websites = $this->getWebsitesProductAttachedTo($entity, $context['channel']);
        $status = $entity->getStatus();

        return new ProductSimpleCreateDTO(
            $entity->getId(),
            $sku,
            $name,
            $websites,
            $status
        );
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
}
