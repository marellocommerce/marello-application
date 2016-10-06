<?php

namespace Marello\Bundle\ShippingBundle\Entity;

use Marello\Bundle\ShippingBundle\Entity\Shipment;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ExtendShipmentAware
 *
 * @package Marello\Bundle\ShippingBundle\Entity
 * @ORM\MappedSuperclass
 */
abstract class ExtendShipmentAware
{
    /**
     * @ORM\OneToOne(targetEntity="Marello\Bundle\ShippingBundle\Entity\Shipment")
     * @ORM\JoinColumn(name="shipment_id", referencedColumnName="id", nullable=true)
     *
     * @var Shipment
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
    public function setShipment($shipment)
    {
        $this->shipment = $shipment;

        return $this;
    }
}