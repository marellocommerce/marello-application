<?php

namespace Marello\Bundle\Magento2Bundle\Form\DataTransformer;

use Marello\Bundle\Magento2Bundle\Model\WebsiteToSalesChannelMapItem;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;

class WebsiteToSalesChannelMapItemCollectionDataTransformer extends CollectionToArrayTransformer
{
    /**
     * {@inheritDoc}
     */
    public function transform($collection)
    {
        $array = parent::transform($collection);

        return \array_map(function (WebsiteToSalesChannelMapItem $websiteToSalesChannelMapItem) {
            return $websiteToSalesChannelMapItem->toArray();
        }, $array);
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($array)
    {
        if ('' !== $array && null !== $array) {
            $array = \array_map(
                WebsiteToSalesChannelMapItem::createFromCallable(),
                (array) $array
            );
        }

        return parent::reverseTransform($array);
    }
}
