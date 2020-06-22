<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Context\Factory;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;

interface GoogleApiContextFactoryInterface
{
    /**
     * @param MarelloAddress $originAddress
     * @param MarelloAddress|null $destinationAddress
     * @return GoogleApiContextInterface
     */
    public static function createContext(MarelloAddress $originAddress, MarelloAddress $destinationAddress = null);
}
