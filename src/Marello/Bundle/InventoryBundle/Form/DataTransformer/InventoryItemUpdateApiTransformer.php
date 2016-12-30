<?php

namespace Marello\Bundle\InventoryBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryItemUpdateApi;

class InventoryItemUpdateApiTransformer implements DataTransformerInterface
{
    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value) {
            return new InventoryItemUpdateApi(null, $this->eventDispatcher);
        }

        if (!$value instanceof InventoryItem) {
            throw new TransformationFailedException();
        }

        return new InventoryItemUpdateApi($value, $this->eventDispatcher);
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
