<?php

namespace Marello\Bundle\UPSBundle\Method\Factory;

use Marello\Bundle\UPSBundle\Event\MethodTypesBeforeCreateEvent;
use Marello\Bundle\UPSBundle\Factory\UPSRequestFactoryInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\IntegrationBundle\Provider\IntegrationIconProviderInterface;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Marello\Bundle\ShippingBundle\Method\Factory\IntegrationShippingMethodFactoryInterface;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCache;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Method\UPSShippingMethod;
use Marello\Bundle\UPSBundle\Provider\UPSTransport;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UPSShippingMethodFactory implements IntegrationShippingMethodFactoryInterface
{
    /**
     * @var UPSTransport
     */
    private $transport;

    /**
     * @var UPSRequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var LocalizationHelper
     */
    private $localizationHelper;

    /**
     * @var ShippingPriceCache
     */
    private $shippingPriceCache;

    /**
     * @var IntegrationIdentifierGeneratorInterface
     */
    private $integrationIdentifierGenerator;

    /**
     * @var UPSShippingMethodTypeFactoryInterface
     */
    private $methodTypeFactory;

    /**
     * @var IntegrationIconProviderInterface
     */
    private $integrationIconProvider;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param TransportInterface                      $transport
     * @param UPSRequestFactoryInterface              $requestFactory
     * @param LocalizationHelper                      $localizationHelper
     * @param IntegrationIconProviderInterface        $integrationIconProvider
     * @param ShippingPriceCache                      $shippingPriceCache
     * @param IntegrationIdentifierGeneratorInterface $integrationIdentifierGenerator
     * @param UPSShippingMethodTypeFactoryInterface   $methodTypeFactory
     * @param EventDispatcherInterface                $eventDispatcher
     */
    public function __construct(
        TransportInterface $transport,
        UPSRequestFactoryInterface $requestFactory,
        LocalizationHelper $localizationHelper,
        IntegrationIconProviderInterface $integrationIconProvider,
        ShippingPriceCache $shippingPriceCache,
        IntegrationIdentifierGeneratorInterface $integrationIdentifierGenerator,
        UPSShippingMethodTypeFactoryInterface $methodTypeFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->transport = $transport;
        $this->requestFactory = $requestFactory;
        $this->localizationHelper = $localizationHelper;
        $this->shippingPriceCache = $shippingPriceCache;
        $this->integrationIdentifierGenerator = $integrationIdentifierGenerator;
        $this->methodTypeFactory = $methodTypeFactory;
        $this->integrationIconProvider = $integrationIconProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function create(Channel $channel)
    {
        return new UPSShippingMethod(
            $this->getIdentifier($channel),
            $this->getLabel($channel),
            $this->getIcon($channel),
            $this->createTypes($channel),
            $this->getSettings($channel),
            $this->transport,
            $this->requestFactory,
            $this->shippingPriceCache,
            $channel->isEnabled()
        );
    }

    /**
     * @param Channel $channel
     * @return string
     */
    private function getIdentifier(Channel $channel)
    {
        return $this->integrationIdentifierGenerator->generateIdentifier($channel);
    }

    /**
     * @param Channel $channel
     * @return string
     */
    private function getLabel(Channel $channel)
    {
        $settings = $this->getSettings($channel);

        return (string)$this->localizationHelper->getLocalizedValue($settings->getLabels());
    }

    /**
     * @param Channel $channel
     * @return \Oro\Bundle\IntegrationBundle\Entity\Transport|UPSSettings
     */
    private function getSettings(Channel $channel)
    {
        return $channel->getTransport();
    }

    /**
     * @param Channel $channel
     * @return array
     */
    private function createTypes(Channel $channel)
    {
        $applicableShippingServices = $this->getSettings($channel)->getApplicableShippingServices()->toArray();

        $event = new MethodTypesBeforeCreateEvent($channel, $applicableShippingServices);
        $this->eventDispatcher->dispatch(MethodTypesBeforeCreateEvent::NAME, $event);
        
        return array_map(function (ShippingService $shippingService) use ($channel) {
            return $this->methodTypeFactory->create($channel, $shippingService);
        }, $event->getShippingServices());
    }

    /**
     * @param Channel $channel
     *
     * @return string|null
     */
    private function getIcon(Channel $channel)
    {
        return $this->integrationIconProvider->getIcon($channel);
    }
}
