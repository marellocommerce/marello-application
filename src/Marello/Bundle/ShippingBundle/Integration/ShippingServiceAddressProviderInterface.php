<?php

namespace Marello\Bundle\ShippingBundle\Integration;

use Marello\Bundle\ShippingBundle\Integration\ShippingAwareInterface;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

interface ShippingServiceAddressProviderInterface
{
    /**
     * @return MarelloAddress
     */
    public function getShipFrom(ShippingAwareInterface $shippingAwareInterface);

    /**
     * @return MarelloAddress
     */
    public function getShipTo(ShippingAwareInterface $shippingAwareInterface);
}