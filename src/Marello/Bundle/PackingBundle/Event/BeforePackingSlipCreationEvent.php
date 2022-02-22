<?php

namespace Marello\Bundle\PackingBundle\Event;

use Marello\Bundle\OrderBundle\Entity\Order;
use Symfony\Contracts\EventDispatcher\Event;

class BeforePackingSlipCreationEvent extends Event
{
    const NAME = 'marello_packing.before_packing_slip_creation';

    /**
     * @var Order
     */
    private $order;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
