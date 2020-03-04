<?php

namespace Marello\Bundle\UPSBundle\Factory;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;

class CompositeUPSRequestFactory implements UPSRequestFactoryInterface
{
    const REQUEST_CLASS_FIELD = 'requestClass';

    /**
     * @var UPSRequestFactoryInterface[]
     */
    private $factories;

    /**
     * @param UPSRequestFactoryInterface $factory
     * @param $requestClass
     */
    public function addFactory(UPSRequestFactoryInterface $factory, $requestClass)
    {
        $this->factories[$requestClass] = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        UPSSettings $transport,
        ShippingContextInterface $context,
        array $extraParameters = [],
        ShippingService $shippingService = null
    ) {
        if (!isset($extraParameters[self::REQUEST_CLASS_FIELD])) {
            throw new \UnexpectedValueException('RequestClass is not defined');
        }

        return $this
            ->factories[$extraParameters[self::REQUEST_CLASS_FIELD]]
            ->create($transport, $context, $extraParameters, $shippingService);
    }
}
