<?php

namespace Marello\Bundle\ShippingBundle\Integration\UPS;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;

class UPSShippingServiceDataFactory implements ShippingServiceDataFactoryInterface
{

    /**
     * @param Order $order
     *
     * @return array
     */
    public function createData(Order $order)
    {
        // TODO: Implement createData() method.
    }
}
