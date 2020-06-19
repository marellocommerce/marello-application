<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Strategy;

use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class WebsiteMagento2ImportStrategy extends DefaultMagento2ImportStrategy
{
    /**
     * @param Website $entity
     * @return Website|null
     */
    protected function afterProcessEntity($entity)
    {
        $entity = parent::afterProcessEntity($entity);
        if (null === $entity || null === $entity->getSalesChannel()) {
            return $entity;
        }

        /** @var SalesChannel $salesChannel */
        $salesChannel = $entity->getSalesChannel();

        /**
         * Replaces unset all other sales channels except current
         */
        $salesChannel->setMagento2Websites([$entity]);

        return $entity;
    }
}
