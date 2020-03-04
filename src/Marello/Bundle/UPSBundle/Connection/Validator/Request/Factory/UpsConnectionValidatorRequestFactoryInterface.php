<?php

namespace Marello\Bundle\UPSBundle\Connection\Validator\Request\Factory;

use Marello\Bundle\UPSBundle\Client\Request\UpsClientRequestInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;

interface UpsConnectionValidatorRequestFactoryInterface
{
    /**
     * @param UPSSettings $transport
     *
     * @return UpsClientRequestInterface
     */
    public function createByTransport(UPSSettings $transport);
}
