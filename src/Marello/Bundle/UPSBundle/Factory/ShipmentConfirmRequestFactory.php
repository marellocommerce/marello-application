<?php

namespace Marello\Bundle\UPSBundle\Factory;

use Marello\Bundle\UPSBundle\Model\Request\ShipmentConfirmRequest;

class ShipmentConfirmRequestFactory extends PriceRequestFactory
{
    /**
     * {@inheritdoc}
     */
    protected function getRequestClass()
    {
        return ShipmentConfirmRequest::class;
    }
}
