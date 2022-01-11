<?php

namespace Marello\Bundle\OrderBundle\Event;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Symfony\Contracts\EventDispatcher\Event;

class OrderShippingContextBuildingEvent extends Event
{
    const NAME = 'marello_order.order_shipping_context_building';

    /**
     * @var ShippingContextInterface
     */
    private $shippingContext;

    /**
     * @param ShippingContextInterface $shippingContext
     */
    public function __construct(ShippingContextInterface $shippingContext)
    {
        $this->shippingContext = $shippingContext;
    }

    /**
     * @return ShippingContextInterface
     */
    public function getShippingContext()
    {
        return $this->shippingContext;
    }

    /**
     * @param ShippingContextInterface $shippingContext
     * @return $this
     */
    public function setShippingContextBuilder(ShippingContextInterface $shippingContext)
    {
        $this->shippingContext = $shippingContext;
        
        return $this;
    }
}
