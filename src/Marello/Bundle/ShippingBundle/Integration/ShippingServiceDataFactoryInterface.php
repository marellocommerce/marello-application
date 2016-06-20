<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\OrderBundle\Entity\Order;

interface ShippingServiceDataFactoryInterface
{
    /**
     * @param Order $order
     *
     * @return array
     */
    public function createData(Order $order);
}
