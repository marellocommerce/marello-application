<?php

namespace Marello\Bundle\InventoryBundle\Form\DataTransformer;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryItemUpdateApi;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class InventoryItemUpdateApiTransformer implements DataTransformerInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value) {
            return new InventoryItemUpdateApi();
        }

        if (!$value instanceof InventoryItem) {
            throw new TransformationFailedException();
        }

        return new InventoryItemUpdateApi($value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof InventoryItemUpdateApi) {
            throw new TransformationFailedException();
        }

        return $value->toInventoryItem();
    }
}
