<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Translator\SimpleProduct;

use Marello\Bundle\Magento2Bundle\DTO\ProductSimpleCreateDTO;
use Marello\Bundle\ProductBundle\Entity\Product;

class CreateTranslator extends ProductTranslator
{
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

        $websites = $this->getWebsitesProductAttachedTo($entity, $context['channel']);
        $status = $entity->getStatus();

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

        $balancedInventoryLevel = $this->getBalancedInventoryLevel($entity, current($websites));
        $productDTO = new ProductSimpleCreateDTO(
            $entity,
            $websites,
            $status,
            $entity->getInventoryItems()->first(),
            $balancedInventoryLevel
        );

        return $productDTO;
    }
}
