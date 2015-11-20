<?php

namespace Marello\Bundle\InventoryBundle\Form\DataTransformer;

use Marello\Bundle\InventoryBundle\Model\InventoryItemModify;
use Symfony\Component\Form\DataTransformerInterface;

class InventoryItemModifyTransformer implements DataTransformerInterface
{

    /**
     * @param InventoryItem $value
     *
     * @return InventoryItemModify
     */
    public function transform($value)
    {
        if (!$value) {
            return null;
        }

        return new InventoryItemModify($value);
    }

    /**
     * @param InventoryItemModify $value
     *
     * @return InventoryItem
     */
    public function reverseTransform($value)
    {
        $inventoryItem = $value->modify()->getInventoryItem();

        if ($inventoryItem->getQuantity()) {
            return $inventoryItem;
        }

        return null;
    }
}
