<?php
/**
 * Created by PhpStorm.
 * User: jaimy
 * Date: 16/12/16
 * Time: 11:21
 */

namespace Marello\Bundle\InventoryBundle\Manager\Balancer;


use Marello\Bundle\ProductBundle\Entity\Product;

class SingleWarehouseBalancer extends AbstractInventoryBalancer
{
    protected function balanceInventory($context)
    {
        $itemsToUpdate = $this->getInventoryItem($context);
    }

    private function getInventoryItem($context)
    {
        $product = $context->getValue('product');
        if (!$product instanceof Product) {
            throw new \Exception(sprintf('Cannot get inventory items, value for product is not an instance of %s', Product::class));
        }

        return $product->getInventoryItems()->first();
    }
}