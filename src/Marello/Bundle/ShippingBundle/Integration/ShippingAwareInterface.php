<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\ShippingBundle\Entity\Shipment;

interface ShippingAwareInterface
{
    /**
     * @return Shipment|null
     */
    public function getShipment();

    /**
     * @param Shipment|null $shipment
     * @return $this
     */
    public function setShipment(Shipment $shipment = null);
}
