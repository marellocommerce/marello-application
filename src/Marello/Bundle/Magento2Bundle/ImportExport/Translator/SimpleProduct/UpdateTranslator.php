<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Translator\SimpleProduct;

use Marello\Bundle\Magento2Bundle\DTO\ProductSimpleUpdateDTO;
use Marello\Bundle\ProductBundle\Entity\Product;

class UpdateTranslator extends CreateTranslator
{
    /**
     * @param Product $entity
     * @param array $context
     * @return ProductSimpleUpdateDTO|null
     */
    public function translate($entity, array $context = [])
    {
        /**
         * @todo Throw exception in case when input data doesn't fix requirements
         */
        if (!$entity instanceof Product || empty($context['channel'])) {
            return null;
        }

        /**
         * @todo Check possibility to change sku
         */
        $sku = $entity->getSku();
        $name = $entity->getDefaultName();
        $websites = $this->getWebsitesProductAttachedTo($entity, $context['channel']);
        $status = $entity->getStatus();

        return new ProductSimpleUpdateDTO(
            $entity->getId(),
            $sku,
            $name,
            $websites,
            $status
        );
    }
}
