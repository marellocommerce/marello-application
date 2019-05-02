<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Distance;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

interface AddressesDistanceCalculatorInterface
{
    /**
     * @param MarelloAddress $originAddress
     * @param MarelloAddress $destinationAddress
     * @return float
     */
    public function calculate(MarelloAddress $originAddress, MarelloAddress $destinationAddress);
}
