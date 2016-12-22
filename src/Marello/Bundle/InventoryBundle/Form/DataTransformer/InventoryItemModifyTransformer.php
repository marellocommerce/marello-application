<?php

namespace Marello\Bundle\InventoryBundle\Form\DataTransformer;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryItemModify;

class InventoryItemModifyTransformer implements DataTransformerInterface
{
    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function transform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof InventoryItem) {
            throw new TransformationFailedException();
        }

        return new InventoryItemModify($value, $this->eventDispatcher);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof InventoryItemModify) {
            throw new TransformationFailedException();
        }

        return $value->toModifiedInventoryItem();
    }
}
