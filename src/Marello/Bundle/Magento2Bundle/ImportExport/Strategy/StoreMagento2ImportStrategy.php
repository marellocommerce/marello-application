<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Strategy;

use Oro\Bundle\LocaleBundle\Entity\Localization;

class StoreMagento2ImportStrategy extends DefaultMagento2ImportStrategy
{
    /**
     * {@inheritDoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = null;

        if ($entity instanceof Localization && null !== $entity->getFormattingCode()) {
            //for Localization we looking for the first localization by formattingCode
            return $this->databaseHelper->findOneBy(
                Localization::class,
                [
                    'formattingCode' => $entity->getFormattingCode()
                ]
            );
        }

        return parent::findExistingEntity($entity, $searchContext);
    }
}
