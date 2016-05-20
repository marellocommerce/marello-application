<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\ShippingBundle\Entity\Shipment;

interface ShippingServiceIntegrationInterface
{
    /**
     * @param array $data
     *
     * @return Shipment
     */
    public function requestShipment(array $data);

    /**
     * @param Shipment $shipment
     *
     * @return mixed
     */
    public function confirmShipment(Shipment $shipment);
}
