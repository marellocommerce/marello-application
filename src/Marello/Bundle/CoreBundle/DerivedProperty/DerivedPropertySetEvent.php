<?php

namespace Marello\Bundle\CoreBundle\DerivedProperty;

use Symfony\Component\EventDispatcher\Event;

class DerivedPropertySetEvent extends Event
{
    const NAME = 'marello.core.derived_property_set';

    /** @var DerivedPropertyAwareInterface */
    private $entity;

    /**
     * DerivedPropertySetEvent constructor.
     *
     * @param DerivedPropertyAwareInterface $entity
     */
    public function __construct(DerivedPropertyAwareInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return DerivedPropertyAwareInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
