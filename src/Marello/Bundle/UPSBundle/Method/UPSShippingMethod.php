<?php

namespace Marello\Bundle\UPSBundle\Method;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Method\PricesAwareShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodIconAwareInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodTypeInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingTrackingAwareInterface;
use Marello\Bundle\UPSBundle\Cache\ShippingPriceCache;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Marello\Bundle\UPSBundle\Factory\CompositeUPSRequestFactory;
use Marello\Bundle\UPSBundle\Factory\PriceRequestFactory;
use Marello\Bundle\UPSBundle\Factory\UPSRequestFactoryInterface;
use Marello\Bundle\UPSBundle\Form\Type\UPSShippingMethodOptionsType;
use Marello\Bundle\UPSBundle\Model\Request\PriceRequest;
use Marello\Bundle\UPSBundle\Provider\UPSTransport as UPSTransportProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

class UPSShippingMethod implements
    ShippingMethodInterface,
    ShippingMethodIconAwareInterface,
    PricesAwareShippingMethodInterface,
    ShippingTrackingAwareInterface
{
    const IDENTIFIER = 'marello_ups';
    const OPTION_SURCHARGE = 'surcharge';
    const REQUEST_OPTION = 'Shop';

    const TRACKING_URL = 'https://www.ups.com/WebTracking/processInputRequest?TypeOfInquiryNumber=T&InquiryNumber1=';
    const TRACKING_REGEX = '/\b
                            (1Z ?[0-9A-Z]{3} ?[0-9A-Z]{3} 
                            ?[0-9A-Z]{2} ?[0-9A-Z]{4} ?[0-9A-Z]{3} ?[0-9A-Z]|
                            [\dT]\d\d\d ?\d\d\d\d ?\d\d\d)
                            \b/ix';

    /**
     * @var UPSTransportProvider
     */
    protected $transportProvider;

    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var UPSRequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var ShippingPriceCache
     */
    protected $cache;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string|null
     */
    private $icon;

    /**
     * @var array
     */
    private $types;

    /**
     * @var UPSSettings
     */
    private $transport;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @param string                      $identifier
     * @param string                      $label
     * @param string|null                 $icon
     * @param array                       $types
     * @param Transport                   $transport
     * @param UPSTransportProvider        $transportProvider
     * @param UPSRequestFactoryInterface  $requestFactory
     * @param ShippingPriceCache          $cache
     * @param bool                        $enabled
     */
    public function __construct(
        $identifier,
        $label,
        $icon,
        array $types,
        Transport $transport,
        UPSTransportProvider $transportProvider,
        UPSRequestFactoryInterface  $requestFactory,
        ShippingPriceCache $cache,
        $enabled
    ) {
        $this->identifier = $identifier;
        $this->label = $label;
        $this->icon = $icon;
        $this->types = $types;
        $this->transport = $transport;
        $this->transportProvider = $transportProvider;
        $this->requestFactory = $requestFactory;
        $this->cache = $cache;
        $this->enabled = $enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function isGrouped()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritDoc}
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return ShippingMethodTypeInterface[]|array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param string $identifier
     * @return UPSShippingMethodType|null
     */
    public function getType($identifier)
    {
        $methodTypes = $this->getTypes();
        if ($methodTypes !== null) {
            foreach ($methodTypes as $methodType) {
                if ($methodType->getIdentifier() === (string)$identifier) {
                    return $methodType;
                }
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getOptionsConfigurationFormType()
    {
        return UPSShippingMethodOptionsType::class;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return 20;
    }

    /**
     * {@inheritDoc}
     */
    public function calculatePrices(ShippingContextInterface $context, array $methodOptions, array $optionsByTypes)
    {
        $optionsDefaults = [static::OPTION_SURCHARGE => 0];
        $methodOptions = array_merge($optionsDefaults, $methodOptions);

        if (count($this->getTypes()) < 1) {
            return [];
        }

        $prices = $this->fetchPrices($context, array_keys($optionsByTypes));

        foreach ($prices as $typeId => $price) {
            $typeOptions = array_merge($optionsDefaults, $optionsByTypes[$typeId]);
            $prices[$typeId] = $price
                ->setValue(array_sum([
                    (float)$price->getValue(),
                    (float)$methodOptions[static::OPTION_SURCHARGE],
                    (float)$typeOptions[static::OPTION_SURCHARGE]
                ]));
        }

        return $prices;
    }

    /**
     * @param string $number
     * @return string|null
     */
    public function getTrackingLink($number)
    {
        if (!preg_match(self::TRACKING_REGEX, $number, $match)) {
            return null;
        }

        return self::TRACKING_URL . $match[0];
    }

    /**
     * @param ShippingContextInterface $context
     * @param array                    $types
     * @return array
     */
    private function fetchPrices(ShippingContextInterface $context, array $types)
    {
        $prices = [];

        $transport = $this->transport;
        /** @var PriceRequest $priceRequest */
        $priceRequest = $this->requestFactory->create(
            $transport,
            $context,
            [
                CompositeUPSRequestFactory::REQUEST_CLASS_FIELD => PriceRequest::class,
                PriceRequestFactory::REQUEST_OPTION_FIELD => self::REQUEST_OPTION
            ]
        );
        if (!$priceRequest) {
            return $prices;
        }

        $cacheKey = $this->cache->createKey($transport, $priceRequest, $this->getIdentifier(), null);

        foreach ($types as $typeId) {
            $cacheKey->setTypeId($typeId);
            if ($this->cache->containsPrice($cacheKey)) {
                $prices[$typeId] = $this->cache->fetchPrice($cacheKey);
            }
        }

        $notCachedTypes = array_diff($types, array_keys($prices));
        $notCachedTypesNumber = count($notCachedTypes);

        if ($notCachedTypesNumber > 0) {
            if ($notCachedTypesNumber === 1) {
                $typeId = reset($notCachedTypes);
                $shippingService = $this->getType($typeId)->getShippingService();
                $priceRequest->setServiceCode($shippingService->getCode())
                    ->setServiceDescription($shippingService->getDescription());
            }
            $priceResponse = $this->transportProvider->getPriceResponse($priceRequest, $transport);
            if ($priceResponse) {
                foreach ($notCachedTypes as $typeId) {
                    $price = $priceResponse->getPriceByService($typeId);
                    if ($price) {
                        $cacheKey->setTypeId($typeId);
                        $this->cache->savePrice($cacheKey, $price);
                        $prices[$typeId] = $price;
                    }
                }
            }
        }

        return $prices;
    }
}
