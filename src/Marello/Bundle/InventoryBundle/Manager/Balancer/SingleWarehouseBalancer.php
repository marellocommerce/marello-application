<?php

namespace Marello\Bundle\InventoryBundle\Manager\Balancer;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class SingleWarehouseBalancer extends AbstractInventoryBalancer
{
    /**
     * @param InventoryUpdateContext $context
     */
    protected function balanceInventory($context)
    {
        $items = $this->getInventoryItems($context);
        $formattedItems[] = [
            'item'          => $items->first(),
            'qty'           => $context->getStock(),
            'allocatedQty'  => $context->getAllocatedStock()
        ];

        $this->context->setItems($formattedItems);
    }

    /**
     * @param InventoryUpdateContext $context
     * @return mixed
     * @throws \Exception
     */
    private function getInventoryItems($context)
    {
        $product = $context->getProduct();

        if (!$product instanceof Product) {
            throw new \Exception(sprintf('Cannot get inventory items, value for product is not an instance of %s', Product::class));
        }

        return $product->getInventoryItems();
    }
}
