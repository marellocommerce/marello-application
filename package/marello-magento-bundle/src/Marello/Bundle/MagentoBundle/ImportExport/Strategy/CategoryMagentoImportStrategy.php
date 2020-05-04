<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Strategy;

use Marello\Bundle\MagentoBundle\Entity\Category;

class CategoryMagentoImportStrategy extends DefaultMagentoImportStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = null;

        if ($entity instanceof Category) {
            $existingEntity = $this->databaseHelper->findOneBy(
                Category::class,
                [
                    'originId' => $entity->getOriginId(),
                    'code' => $entity->getCode()
                ]
            );
            return $existingEntity;
        }
        return parent::findExistingEntity($entity, $searchContext);
    }
}
