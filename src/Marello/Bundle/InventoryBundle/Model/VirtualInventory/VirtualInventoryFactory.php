<?php

namespace Marello\Bundle\InventoryBundle\Model\VirtualInventory;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;

class VirtualInventoryFactory
{
    /**
     * @param ProductInterface $product
     * @param SalesChannelGroup $group
     * @param $inventoryQty
     * @return VirtualInventoryLevel
     */
    public function create(ProductInterface $product, SalesChannelGroup $group, $inventoryQty)
    {
        return new VirtualInventoryLevel($product, $group, $inventoryQty);
    }
}
