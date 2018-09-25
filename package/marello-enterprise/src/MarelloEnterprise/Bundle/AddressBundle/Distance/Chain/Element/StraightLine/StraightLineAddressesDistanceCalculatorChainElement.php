<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\StraightLine;

use Symfony\Component\HttpFoundation\Session\Session;

use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Exception\GoogleApiException;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use MarelloEnterprise\Bundle\AddressBundle\Provider\AddressCoordinatesProviderInerface;
use MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\AbstractAddressesDistanceCalculatorChainElement;

class StraightLineAddressesDistanceCalculatorChainElement extends AbstractAddressesDistanceCalculatorChainElement implements
    FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;

    /**
     * @var AddressCoordinatesProviderInerface
     */
    private $coordinatesProvider;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param AddressCoordinatesProviderInerface $coordinatesProvider
     * @param Session $session
     */
    public function __construct(AddressCoordinatesProviderInerface $coordinatesProvider, Session $session)
    {
        $this->coordinatesProvider = $coordinatesProvider;
        $this->session = $session;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getDistance(
        MarelloAddress $originAddress,
        MarelloAddress $destinationAddress,
        $unit = 'metric'
    ) {
        if ($this->featureChecker->isFeatureEnabled('address_geocoding')) {
            try {
                $originCoordinates = $this->coordinatesProvider->getCoordinates($originAddress);
                $destinationCoordinates = $this->coordinatesProvider->getCoordinates($destinationAddress);

                if (!$originCoordinates || !$destinationCoordinates ||
                    empty($originCoordinates || empty($destinationCoordinates))
                ) {
                    return null;
                }

                $lat1 = $originCoordinates[GeocodingApiResultFactory::LATITUDE];
                $lon1 = $originCoordinates[GeocodingApiResultFactory::LONGITUDE];
                $lat2 = $destinationCoordinates[GeocodingApiResultFactory::LATITUDE];
                $lon2 = $destinationCoordinates[GeocodingApiResultFactory::LONGITUDE];

                $theta = $lon1 - $lon2;
                $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
                    * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $unit = strtolower($unit);

                if ($unit == "metric") {
                    return ($miles * 1.609344);
                } else {
                    return $miles;
                }
            } catch (GoogleApiException $e) {
                $this->session->getFlashBag()->add('warning', $e->getMessage());
            }
        }

        return null;
    }
}
