<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Strategy;

use Marello\Bundle\MagentoBundle\Entity\Product;

class ProductMagentoImportStrategy extends DefaultMagentoImportStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = null;

        if ($entity instanceof Product) {
            $existingEntity = $this->databaseHelper->findOneBy(
                Product::class,
                [
                    'originId' => $entity->getOriginId(),
                    'sku' => $entity->getSku()
                ]
            );
            return $existingEntity;
        }
        return parent::findExistingEntity($entity, $searchContext);
    }
}
