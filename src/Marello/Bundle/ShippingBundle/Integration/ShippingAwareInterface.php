<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\ShippingBundle\Entity\Shipment;

interface ShippingAwareInterface
{
    /**
     * @return string
     */
    public function getShippingWeight();

    /**
     * @return string
     */
    public function getShippingDescription();
}