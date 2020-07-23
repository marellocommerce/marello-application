<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Translator\SimpleProduct;

use Marello\Bundle\Magento2Bundle\DTO\ProductSimpleUpdateWebsiteScopeDTO;
use Marello\Bundle\Magento2Bundle\Entity\Product as MagentoProduct;
use Marello\Bundle\Magento2Bundle\Entity\Repository\ProductRepository as MagentoProductRepository;
use Marello\Bundle\Magento2Bundle\Entity\Repository\WebsiteRepository;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\Magento2Bundle\ImportExport\Translator\TranslatorInterface;
use Marello\Bundle\PricingBundle\Entity\PriceListInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class UpdateWebsiteScopeTranslator implements TranslatorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var WebsiteRepository */
    protected $websiteRepository;

    /** @var MagentoProductRepository */
    protected $magentoProductRepository;

    /**
     * @param WebsiteRepository $websiteRepository
     * @param MagentoProductRepository $magentoProductRepository
     */
    public function __construct(
        WebsiteRepository $websiteRepository,
        MagentoProductRepository $magentoProductRepository
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->magentoProductRepository = $magentoProductRepository;
    }

    /**
     * @param Product $entity
     * @param array $context
     * @return ProductSimpleUpdateWebsiteScopeDTO|null
     */
    public function translate($entity, array $context = [])
    {
        $websiteId = $context['website'] instanceof Website ? ($context['website'])->getId() : $context['website'];
        if (!$entity instanceof Product || empty($context['channel']) || empty($context['website'])) {
            $this->logger->warning(
                '[Magento 2] Input data doesn\'t fit to requirements. ' .
                'Skip to update website scope data for remote product.',
                [
                    'entity_type' => is_object($entity) ? get_class($entity) : gettype($entity),
                    'product_id' => $entity instanceof Product ? $entity->getId() : null,
                    'website_id' => $websiteId,
                    'integration_channel_id' => $context['channel']
                ]
            );

            return null;
        }

        $internalMagentoProduct = $this->magentoProductRepository->findOneBy(
            [
                'channel' => $context['channel'],
                'product' => $entity->getId()
            ]
        );

        if (!$internalMagentoProduct instanceof MagentoProduct) {
            $this->logger->warning(
                '[Magento 2] Can\'t find Magento product. ' .
                'Skip to update website scope data for remote product.',
                [
                    'product_id' => $entity instanceof Product ? $entity->getId() : null,
                    'integration_channel_id' => $context['channel'],
                    'website_id' => $websiteId,
                ]
            );

            return null;
        }

        if ($context['website'] instanceof Website) {
            $website = $context['website'];
        } else {
            $website = $this->websiteRepository->find($context['website']);
        }

        if (null === $website || null === $website->getSalesChannel()) {
            $this->logger->warning(
                '[Magento 2] No website found or website won\'t attached to any sales channels. ' .
                'Skip to update website scope data for remote product.',
                [
                    'product_id' => $entity->getId(),
                    'website_id' => $websiteId,
                    'integration_channel_id' => $context['channel'],
                ]
            );

            return null;
        }

        $priceList = $entity->getSalesChannelPrice($website->getSalesChannel());

        $defaultPrice = null;
        $specialPrice = null;
        if ($priceList instanceof PriceListInterface) {
            $defaultPrice = $priceList->getDefaultPrice()->getValue();

            if (null !== $priceList->getSpecialPrice()) {
                $specialPrice = $priceList->getSpecialPrice()->getValue();
            }
        }

        return new ProductSimpleUpdateWebsiteScopeDTO(
            $internalMagentoProduct,
            $entity,
            $website,
            $defaultPrice,
            $specialPrice
        );
    }
}
