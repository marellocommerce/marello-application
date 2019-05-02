<?php

namespace Marello\Bundle\InventoryBundle\Model\BalancedInventory;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;

class BalancedInventoryFactory
{
    /**
     * @param ProductInterface $product
     * @param SalesChannelGroup $group
     * @param $inventoryQty
     * @return BalancedInventoryLevel
     */
    public function create(ProductInterface $product, SalesChannelGroup $group, $inventoryQty)
    {
        return new BalancedInventoryLevel($product, $group, $inventoryQty);
    }
}
