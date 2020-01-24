<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Context\Factory;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContext;

class GoogleApiContextFactory implements GoogleApiContextFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public static function createContext(MarelloAddress $originAddress, MarelloAddress $destinationAddress = null)
    {
        return new GoogleApiContext([
            GoogleApiContext::FIELD_ORIGIN_ADDRESS => $originAddress,
            GoogleApiContext::FIELD_DESTINATION_ADDRESS => $destinationAddress
        ]);
    }
}
