<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Translator;

use Marello\Bundle\Magento2Bundle\DTO\ProductDeleteOnChannelDTO;
use Marello\Bundle\Magento2Bundle\Model\Magento2TransportSettings;
use Marello\Bundle\Magento2Bundle\Entity\Product as MagentoProduct;
use Marello\Bundle\Magento2Bundle\Provider\TrackedSalesChannelProvider;
use Marello\Bundle\Magento2Bundle\Entity\Repository\ProductRepository as MagentoProductRepository;
use Marello\Bundle\ProductBundle\Entity\Product;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ProductDeleteOnChannelTranslator implements TranslatorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var MagentoProductRepository */
    protected $magentoProductRepository;

    /** @var TrackedSalesChannelProvider */
    protected $salesChannelProvider;

    /**
     * @param MagentoProductRepository $magentoProductRepository
     * @param TrackedSalesChannelProvider $salesChannelProvider
     */
    public function __construct(
        MagentoProductRepository $magentoProductRepository,
        TrackedSalesChannelProvider $salesChannelProvider
    ) {
        $this->magentoProductRepository = $magentoProductRepository;
        $this->salesChannelProvider = $salesChannelProvider;
    }

    /**
     * @param Product $entity
     * @param array $context
     * @return ProductDeleteOnChannelDTO|null
     */
    public function translate($entity, array $context = [])
    {
        if (!$entity instanceof Product || empty($context['channel'])) {
            $this->logger->warning(
                '[Magento 2] Input data doesn\'t fit to requirements. ' .
                'Skip to product delete on channel.',
                [
                    'product_id' => $entity instanceof Product ? $entity->getId() : null,
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
                'Skip to product delete on channel.',
                [
                    'product_id' => $entity instanceof Product ? $entity->getId() : null,
                    'integration_channel_id' => $context['channel']
                ]
            );

            return null;
        }

        $originWebsiteIds = null;
        /** @var Magento2TransportSettings $config */
        $config = $internalMagentoProduct->getChannel()->getTransport()->getSettingsBag();
        if ($config->isDeleteRemoteProductFromWebsiteOnly()) {
            $originWebsiteIds = $this->salesChannelProvider
                ->getSalesChannelIdsByIntegrationId($context['channel']);
        }

        return new ProductDeleteOnChannelDTO(
            $internalMagentoProduct,
            $entity,
            $originWebsiteIds
        );
    }
}
