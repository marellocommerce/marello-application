<?php

namespace Marello\Bundle\UPSBundle\Model\Response;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;

interface UPSResponseInterface
{
    /**
     * @param RestResponseInterface $restResponse
     * @throws \LogicException on UPS fault
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function parse(RestResponseInterface $restResponse);
}
