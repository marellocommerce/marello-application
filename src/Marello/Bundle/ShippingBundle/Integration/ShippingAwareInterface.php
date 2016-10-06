<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\ShippingBundle\Entity\Shipment;

interface ShippingAwareInterface
{
    /**
     * @return MarelloAddress
     */
    public function getShipTo();

    /**
     * @return MarelloAddress
     */
    public function getShipFrom();

    /**
     * @return string
     */
    public function getWeight();

    /**
     * @return string
     */
    public function getDescription();

//    /**
//     * @param Shipment $shipment
//     * @return ShippingAwareInterface
//     */
//    public function setShipment(Shipment $shipment);
//
//    /**
//     * @return Shipment
//     */
//    public function getShipment();
}