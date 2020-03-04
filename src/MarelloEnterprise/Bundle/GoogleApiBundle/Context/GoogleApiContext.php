<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Context;

use Symfony\Component\HttpFoundation\ParameterBag;

class GoogleApiContext extends ParameterBag implements GoogleApiContextInterface
{
    const FIELD_ORIGIN_ADDRESS = 'billing_address';
    const FIELD_DESTINATION_ADDRESS = 'shipping_address';
 
    /**
     * {@inheritdoc}
     */
    public function getOriginAddress()
    {
        return $this->get(self::FIELD_ORIGIN_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function getDestinationAddress()
    {
        return $this->get(self::FIELD_DESTINATION_ADDRESS);
    }
}
