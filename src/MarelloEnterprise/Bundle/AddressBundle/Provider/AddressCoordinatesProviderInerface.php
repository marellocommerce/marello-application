<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Provider;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

interface AddressCoordinatesProviderInerface
{
    /**
     * @param MarelloAddress $address
     * @return array
     */
    public function getCoordinates(MarelloAddress $address);
}
