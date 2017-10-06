<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Context;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

interface GoogleApiContextInterface
{
    /**
     * @return MarelloAddress
     */
    public function getOriginAddress();

    /**
     * @return MarelloAddress|null
     */
    public function getDestinationAddress();
}
