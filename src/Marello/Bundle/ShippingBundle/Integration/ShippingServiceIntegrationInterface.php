<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\ShippingBundle\Entity\Shipment;

interface ShippingServiceIntegrationInterface
{
    /**
     * @param ShippingAwareInterface $shippingAwareInterface
     * @param array $data
     *
     * @return Shipment
     */
    public function createShipment(ShippingAwareInterface $shippingAwareInterface, array $data);
}
