<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\ShippingBundle\Entity\Shipment;

interface ShippingAwareInterface
{
    public function getShipment();

    public function setShipment(?Shipment $shipment);
}
