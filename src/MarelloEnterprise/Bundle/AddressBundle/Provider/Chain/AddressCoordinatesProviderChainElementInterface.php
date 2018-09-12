<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Provider\Chain;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Provider\AddressCoordinatesProviderInerface;

interface AddressCoordinatesProviderChainElementInterface extends AddressCoordinatesProviderInerface
{
    /**
     * @param MarelloAddress $address
     * @return array|null
     */
    public function collectCoordinates(MarelloAddress $address);
}
