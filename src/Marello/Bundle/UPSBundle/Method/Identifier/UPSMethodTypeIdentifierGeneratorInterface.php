<?php

namespace Marello\Bundle\UPSBundle\Method\Identifier;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Marello\Bundle\UPSBundle\Entity\ShippingService;

interface UPSMethodTypeIdentifierGeneratorInterface
{
    /**
     * @param Channel $channel
     * @param ShippingService $service
     * @return string
     */
    public function generateIdentifier(Channel $channel, ShippingService $service);
}
