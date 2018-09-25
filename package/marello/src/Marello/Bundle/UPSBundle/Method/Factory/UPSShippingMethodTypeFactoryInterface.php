<?php

namespace Marello\Bundle\UPSBundle\Method\Factory;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Method\UPSShippingMethodType;

interface UPSShippingMethodTypeFactoryInterface
{
    /**
     * @param Channel $channel
     * @param ShippingService $service
     * @return UPSShippingMethodType
     */
    public function create(Channel $channel, ShippingService $service);
}
