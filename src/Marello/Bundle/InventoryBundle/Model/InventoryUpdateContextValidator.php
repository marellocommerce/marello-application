<?php

namespace Marello\Bundle\InventoryBundle\Model;

class InventoryUpdateContextValidator
{
    /**
     * @deprecated use validateContext(InventoryUpdateContext $context) instead
     * @param InventoryUpdateContext $context
     * @return bool
     */
    public function validateItems(InventoryUpdateContext $context)
    {
        return $this->validateContext($context);
    }

    /**
     * Validate the data structure of the items to be updated
     * @param InventoryUpdateContext $context
     * @return bool
     */
    public function validateContext(InventoryUpdateContext $context)
    {
        $product = $context->getProduct();
        $inventoryItem = $context->getInventoryItem();
        $inventoryLevel = $context->getInventoryLevel();
        if (!$inventoryLevel && !$inventoryItem && !$product) {
            return false;
        }

        $inventory = $context->getInventory();
        if (!is_int($inventory)) {
            return false;
        }

        $allocatedInventory = $context->getAllocatedInventory();
        if (!is_int($allocatedInventory)) {
            return false;
        }

        return true;
    }
}
