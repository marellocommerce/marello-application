<?php

namespace Marello\Bundle\InventoryBundle\Strategy;

use ArrayAccess;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;

interface BalancerStrategyInterface
{
    /**
     * @return string|int
     */
    public function getIdentifier();

    /**
     * @param ProductInterface $product
     * @param ArrayAccess $salesChannelGroups
     * @param $inventory
     * @return mixed
     */
    public function getBalancedResult(ProductInterface $product, ArrayAccess $salesChannelGroups, $inventory);
}
