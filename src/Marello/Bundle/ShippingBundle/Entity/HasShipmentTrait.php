<?php

namespace Marello\Bundle\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait HasShipmentTrait
{
    /**
     * @ORM\OneToOne(targetEntity="Marello\Bundle\ShippingBundle\Entity\Shipment")
     */
    protected $shipment;

    /**
     * @return Shipment
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * @param Shipment $shipment
     *
     * @return $this
     */
    public function setShipment(Shipment $shipment)
    {
        $this->shipment = $shipment;

        return $this;
    }
}
