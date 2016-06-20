<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Entity\Shipment;

interface ShippingServiceIntegrationInterface
{
    /**
     * @param Order $order
     * @param array $data
     *
     * @return Shipment
     */
    public function createShipment(Order $order, array $data);
}
