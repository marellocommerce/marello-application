<?php

namespace Marello\Bundle\UPSBundle\Factory;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Model\Request\UPSRequestInterface;

interface UPSRequestFactoryInterface
{
    /**
     * @param UPSSettings $transport
     * @param ShippingContextInterface $context
     * @param array $extraParameters
     * @param ShippingService|null $shippingService
     * @return UPSRequestInterface|null
     * @throws \UnexpectedValueException
     */
    public function create(
        UPSSettings $transport,
        ShippingContextInterface $context,
        array $extraParameters = [],
        ShippingService $shippingService = null
    );
}
