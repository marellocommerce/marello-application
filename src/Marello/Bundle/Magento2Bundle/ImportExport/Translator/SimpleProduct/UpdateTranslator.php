<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Translator\SimpleProduct;

use Marello\Bundle\Magento2Bundle\DTO\ProductSimpleUpdateDTO;
use Marello\Bundle\Magento2Bundle\Entity\Product as MagentoProduct;
use Marello\Bundle\Magento2Bundle\Entity\Repository\ProductRepository as MagentoProductRepository;
use Marello\Bundle\Magento2Bundle\ImportExport\Helper\SimpleProductTranslatorHelper;
use Marello\Bundle\Magento2Bundle\ImportExport\Translator\TranslatorInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class UpdateTranslator implements TranslatorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var SimpleProductTranslatorHelper */
    protected $helper;

    /** @var MagentoProductRepository */
    protected $magentoProductRepository;

    /**
     * @param SimpleProductTranslatorHelper $helper
     * @param MagentoProductRepository $magentoProductRepository
     */
    public function __construct(
        SimpleProductTranslatorHelper $helper,
        MagentoProductRepository $magentoProductRepository
    ) {
        $this->helper = $helper;
        $this->magentoProductRepository = $magentoProductRepository;
    }

    /**
     * @param Product $entity
     * @param array $context
     * @return ProductSimpleUpdateDTO|null
     */
    public function translate($entity, array $context = [])
    {
        if (!$entity instanceof Product || empty($context['channel'])) {
            $this->logger->warning(
                '[Magento 2] Input data doesn\'t fit to requirements. ' .
                'Skip to update remote product.',
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
                'Skip to update remote product.',
                [
                    'entity_type' => is_object($entity) ? get_class($entity) : gettype($entity),
                    'product_id' => $entity instanceof Product ? $entity->getId() : null,
                    'integration_channel_id' => $context['channel']
                ]
            );

            return null;
        }

        $websites = $this->helper->getWebsitesProductAttachedTo($entity, $context['channel']);
        if (empty($websites)) {
            $this->logger->warning(
                '[Magento 2] No websites attached to the entity with channel. ' .
                'Skip to update remote product.',
                [
                    'product_id' => $entity->getId(),
                    'integration_channel_id' => $context['channel']
                ]
            );

            return null;
        }

        $status = $entity->getStatus();
        $productTaxClass = $this->helper->getMagentoProductTaxClass($entity, $context['channel']);
        $balancedInventoryLevel = $this->helper->getBalancedInventoryLevel($entity, current($websites));
        $inventoryItem = $entity->getInventoryItems()->first();
        return new ProductSimpleUpdateDTO(
            $internalMagentoProduct,
            $entity,
            $websites,
            $status,
            $inventoryItem,
            $productTaxClass,
            $balancedInventoryLevel
        );
    }
}
