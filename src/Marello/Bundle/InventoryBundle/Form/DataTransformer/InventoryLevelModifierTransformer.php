<?php

namespace Marello\Bundle\InventoryBundle\Form\DataTransformer;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelModifier;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class InventoryLevelModifierTransformer implements DataTransformerInterface
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
        var_dump(get_class($value));
        die(__METHOD__);
        if (!$value) {
            return null;
        }

        if (!$value instanceof InventoryLevel) {
            throw new TransformationFailedException();
        }
        $modifier = new InventoryLevelModifier($value);
        $modifier->setEventDispatcher($this->eventDispatcher);

        return $modifier;
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
        var_dump(get_class($value));
        die(__METHOD__);
        if (!$value) {
            return null;
        }

        if (!$value instanceof InventoryLevelModifier) {
            throw new TransformationFailedException();
        }

        return $value->toModifiedInventoryLevel();
    }
}
