<?php

namespace Marello\Bundle\ProductBundle\Util;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class ProductHelper
{
    /**
     * Returns ids of all related sales channels for a product.
     *
     * @param Product $product
     *
     * @return array $ids
     */
    public function getSalesChannelsIds(Product $product)
    {
        $ids = [];
        $product
            ->getChannels()
            ->map(function (SalesChannel $channel) use (&$ids) {
                $ids[] = $channel->getId();
        });

        return $ids;
    }
}
