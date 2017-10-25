<?php

namespace Marello\Bundle\InventoryBundle\Model\InventoryBalancer;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;

class VirtualInventoryFactory
{
    /**
     * @param Product $product
     * @param SalesChannelGroup $group
     * @param $inventoryQty
     * @return VirtualInventoryLevel
     */
    public function create(Product $product, SalesChannelGroup $group, $inventoryQty)
    {
        return new VirtualInventoryLevel($product, $group, $inventoryQty);
    }
}
