<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;

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
