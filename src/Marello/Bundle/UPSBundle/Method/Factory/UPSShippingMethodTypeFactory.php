<?php

namespace Marello\Bundle\UPSBundle\Method\Factory;

use Marello\Bundle\UPSBundle\Factory\UPSRequestFactoryInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCache;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Method\Identifier\UPSMethodTypeIdentifierGeneratorInterface;
use Marello\Bundle\UPSBundle\Method\UPSShippingMethodType;
use Marello\Bundle\UPSBundle\Provider\UPSTransport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

class UPSShippingMethodTypeFactory implements UPSShippingMethodTypeFactoryInterface
{
    /**
     * @var UPSMethodTypeIdentifierGeneratorInterface
     */
    private $typeIdentifierGenerator;

    /**
     * @var IntegrationIdentifierGeneratorInterface
     */
    private $integrationIdentifierGenerator;

    /**
     * @var UPSTransport
     */
    private $transport;

    /**
     * @var UPSRequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var ShippingPriceCache
     */
    private $shippingPriceCache;

    /**
     * @param UPSMethodTypeIdentifierGeneratorInterface $typeIdentifierGenerator
     * @param IntegrationIdentifierGeneratorInterface   $integrationIdentifierGenerator
     * @param TransportInterface                        $transport
     * @param UPSRequestFactoryInterface                $requestFactory
     * @param ShippingPriceCache                        $shippingPriceCache
     */
    public function __construct(
        UPSMethodTypeIdentifierGeneratorInterface $typeIdentifierGenerator,
        IntegrationIdentifierGeneratorInterface $integrationIdentifierGenerator,
        TransportInterface $transport,
        UPSRequestFactoryInterface $requestFactory,
        ShippingPriceCache $shippingPriceCache
    ) {
        $this->typeIdentifierGenerator = $typeIdentifierGenerator;
        $this->integrationIdentifierGenerator = $integrationIdentifierGenerator;
        $this->transport = $transport;
        $this->requestFactory = $requestFactory;
        $this->shippingPriceCache = $shippingPriceCache;
    }

    /**
     * @param Channel $channel
     * @param ShippingService $service
     * @return UPSShippingMethodType
     */
    public function create(Channel $channel, ShippingService $service)
    {
        return new UPSShippingMethodType(
            $this->getIdentifier($channel, $service),
            $this->getLabel($service),
            $this->integrationIdentifierGenerator->generateIdentifier($channel),
            $service,
            $this->getSettings($channel),
            $this->transport,
            $this->requestFactory,
            $this->shippingPriceCache
        );
    }

    /**
     * @param Channel $channel
     * @param ShippingService $service
     * @return string
     */
    private function getIdentifier(Channel $channel, ShippingService $service)
    {
        return $this->typeIdentifierGenerator->generateIdentifier($channel, $service);
    }

    /**
     * @param ShippingService $service
     * @return string
     */
    private function getLabel(ShippingService $service)
    {
        return $service->getDescription();
    }

    /**
     * @param Channel $channel
     * @return \Oro\Bundle\IntegrationBundle\Entity\Transport|UPSSettings
     */
    private function getSettings(Channel $channel)
    {
        return $channel->getTransport();
    }
}
