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

    /** @return bool */
    public function isEnabled();

    /** @return string */
    public function getLabel();

    /**
     * @param ProductInterface $product
     * @param ArrayAccess $salesChannelGroups
     * @param $inventory
     * @return array
     */
    public function getResults(ProductInterface $product, ArrayAccess $salesChannelGroups, $inventory);
}
