<?php

namespace Marello\Bundle\ShippingBundle\Event;

use Marello\Bundle\ShippingBundle\Method\ShippingMethodViewCollection;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is fired by the ShippingPriceProvider after getting all applicable shipping methods.
 * The listeners of this event may modify ShippingMethodViewCollection according to their needs.
 */
class ApplicableMethodsEvent extends Event
{
    const NAME = 'marello_shipping.applicable_methods';

    /**
     * @var ShippingMethodViewCollection
     */
    private $methodCollection;

    /**
     * @var object
     */
    private $sourceEntity;

    /**
     * @param ShippingMethodViewCollection $methodCollection
     * @param object $sourceEntity
     */
    public function __construct(ShippingMethodViewCollection $methodCollection, $sourceEntity)
    {
        $this->methodCollection = $methodCollection;
        $this->sourceEntity = $sourceEntity;
    }

    /**
     * @return ShippingMethodViewCollection
     */
    public function getMethodCollection()
    {
        return $this->methodCollection;
    }

    /**
     * @return object
     */
    public function getSourceEntity()
    {
        return $this->sourceEntity;
    }
}
