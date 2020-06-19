<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Translator\SimpleProduct;

use Marello\Bundle\Magento2Bundle\DTO\ProductSimpleCreateDTO;
use Marello\Bundle\Magento2Bundle\ImportExport\Helper\SimpleProductTranslatorHelper;
use Marello\Bundle\Magento2Bundle\ImportExport\Translator\TranslatorInterface;
use Marello\Bundle\ProductBundle\Entity\Product;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CreateTranslator implements TranslatorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var SimpleProductTranslatorHelper */
    protected $helper;

    /**
     * @param SimpleProductTranslatorHelper $helper
     */
    public function __construct(SimpleProductTranslatorHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Product $entity
     * @param array $context
     * @return ProductSimpleCreateDTO|null
     */
    public function translate($entity, array $context = [])
    {
        if (!$entity instanceof Product || empty($context['channel'])) {
            $this->logger->warning(
                '[Magento 2] Input data doesn\'t fit to requirements. ' .
                'Skip to create remote product.',
                [
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
                'Skip to create remote product.',
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
        $productDTO = new ProductSimpleCreateDTO(
            $entity,
            $websites,
            $status,
            $entity->getInventoryItems()->first(),
            $productTaxClass,
            $balancedInventoryLevel
        );

        return $productDTO;
    }

}
