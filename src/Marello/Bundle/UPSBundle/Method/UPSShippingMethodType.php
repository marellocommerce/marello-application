<?php

namespace Marello\Bundle\UPSBundle\Method;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodTypeInterface;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCache;
use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Factory\CompositeUPSRequestFactory;
use Marello\Bundle\UPSBundle\Factory\PriceRequestFactory;
use Marello\Bundle\UPSBundle\Factory\ShipmentAcceptRequestFactory;
use Marello\Bundle\UPSBundle\Factory\UPSRequestFactoryInterface;
use Marello\Bundle\UPSBundle\Form\Type\UPSShippingMethodOptionsType;
use Marello\Bundle\UPSBundle\Model\Request\PriceRequest;
use Marello\Bundle\UPSBundle\Model\Request\ShipmentAcceptRequest;
use Marello\Bundle\UPSBundle\Model\Request\ShipmentConfirmRequest;
use Marello\Bundle\UPSBundle\Model\Response\ShipmentAcceptResponse;
use Marello\Bundle\UPSBundle\Model\Response\ShipmentConfirmResponse;
use Marello\Bundle\UPSBundle\Provider\UPSTransport as UPSTransportProvider;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

class UPSShippingMethodType implements ShippingMethodTypeInterface
{
    const REQUEST_OPTION = 'Rate';

    /** @var string */
    protected $methodId;

    /** @var UPSSettings */
    protected $transport;

    /** @var UPSTransportProvider */
    protected $transportProvider;

    /** @var ShippingService */
    protected $shippingService;

    /** @var UPSRequestFactoryInterface */
    protected $requestFactory;

    /** @var ShippingPriceCache */
    protected $cache;

    /** @var string */
    private $identifier;

    /** @var string */
    private $label;
    
    /**
     * @param string $identifier
     * @param string $label
     * @param string $methodId
     * @param Transport $transport
     * @param UPSTransportProvider $transportProvider
     * @param ShippingService $shippingService
     * @param UPSRequestFactoryInterface $requestFactory
     * @param ShippingPriceCache $cache
     */
    public function __construct(
        $identifier,
        $label,
        $methodId,
        ShippingService $shippingService,
        Transport $transport,
        UPSTransportProvider $transportProvider,
        UPSRequestFactoryInterface $requestFactory,
        ShippingPriceCache $cache
    ) {
        $this->identifier = $identifier;
        $this->label = $label;
        $this->methodId = $methodId;
        $this->shippingService = $shippingService;
        $this->transport = $transport;
        $this->transportProvider = $transportProvider;
        $this->requestFactory = $requestFactory;
        $this->cache = $cache;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsConfigurationFormType()
    {
        return UPSShippingMethodOptionsType::class;
    }

    /**
     * @return ShippingService
     */
    public function getShippingService()
    {
        return $this->shippingService;
    }

    /**
     * {@inheritdoc}
     */
    public function calculatePrice(ShippingContextInterface $context, array $methodOptions, array $typeOptions)
    {
        /** @var PriceRequest $priceRequest */
        $priceRequest = $this->requestFactory->create(
            $this->transport,
            $context,
            [
                CompositeUPSRequestFactory::REQUEST_CLASS_FIELD => PriceRequest::class,
                PriceRequestFactory::REQUEST_OPTION_FIELD => self::REQUEST_OPTION
            ],
            $this->shippingService
        );

        if (count($priceRequest->getPackages()) < 1) {
            return null;
        }

        $cacheKey = $this->cache->createKey($this->transport, $priceRequest, $this->methodId, $this->getIdentifier());
        if (!$this->cache->containsPrice($cacheKey)) {
            $priceResponse = $this->transportProvider->getPriceResponse($priceRequest, $this->transport);
            if (!$priceResponse) {
                return null;
            }
            $price = $priceResponse->getPriceByService($this->shippingService->getCode());
            if (!$price) {
                return null;
            }
            $this->cache->savePrice($cacheKey, $price);
        } else {
            $price = $this->cache->fetchPrice($cacheKey);
        }

        $optionsDefaults = [
            UPSShippingMethod::OPTION_SURCHARGE => 0,
        ];
        $methodOptions = array_merge($optionsDefaults, $methodOptions);
        $typeOptions = array_merge($optionsDefaults, $typeOptions);

        return $price->setValue(array_sum([
            (float)$price->getValue(),
            (float)$methodOptions[UPSShippingMethod::OPTION_SURCHARGE],
            (float)$typeOptions[UPSShippingMethod::OPTION_SURCHARGE]
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function createShipment(ShippingContextInterface $context, $method, $type)
    {
        /** @var ShipmentConfirmRequest $shipmentConfirmRequest */
        $shipmentConfirmRequest = $this->requestFactory->create(
            $this->transport,
            $context,
            [
                CompositeUPSRequestFactory::REQUEST_CLASS_FIELD => ShipmentConfirmRequest::class
            ],
            $this->shippingService
        );

        /** @var ShipmentConfirmResponse $shipmentConfirmResponse */
        $shipmentConfirmResponse = $this->transportProvider
            ->getShipmentConfirmResponse($shipmentConfirmRequest, $this->transport);

        /** @var ShipmentAcceptRequest $shipmentAcceptRequest */
        $shipmentAcceptRequest = $this->requestFactory->create(
            $this->transport,
            $context,
            [
                CompositeUPSRequestFactory::REQUEST_CLASS_FIELD => ShipmentAcceptRequest::class,
                ShipmentAcceptRequestFactory::SHIPMENT_DIGEST_FIELD => $shipmentConfirmResponse->getShipmentDigest()
            ],
            $this->shippingService
        );

        /** @var ShipmentAcceptResponse $shipmentAcceptResponse */
        $shipmentAcceptResponse = $this->transportProvider
            ->getShipmentAcceptResponse($shipmentAcceptRequest, $this->transport);

        $shipment = new Shipment();

        $shipment
            ->setShippingService(sprintf('%s/%s', $method, $type))
            ->setUpsPackageTrackingNumber($shipmentAcceptResponse->getTrackingNumber())
            ->setIdentificationNumber($shipmentAcceptResponse->getShipmentIdentificationNumber())
            ->setBase64EncodedLabel($shipmentAcceptResponse->getGraphicImage());

        $context->getSourceEntity()->setShipment($shipment);

        return $shipment;
    }
}
