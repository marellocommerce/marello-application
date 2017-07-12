<?php

namespace Marello\Bundle\InventoryBundle\Model;

class InventoryUpdateContextValidator
{
    /**
     * Validate the data structure of the items to be updated
     * @param InventoryUpdateContext $context
     * @return bool
     */
    public function validateItems(InventoryUpdateContext $context)
    {
        $items = $context->getItems();
        foreach ($items as $item) {
            if (!is_array($item)) {
                return false;
            }

            if (!array_key_exists('item', $item)) {
                return false;
            }

            if (!array_key_exists('qty', $item)) {
                return false;
            }

            if (!array_key_exists('allocatedQty', $item)) {
                return false;
            }
        }

        return true;
    }
}
