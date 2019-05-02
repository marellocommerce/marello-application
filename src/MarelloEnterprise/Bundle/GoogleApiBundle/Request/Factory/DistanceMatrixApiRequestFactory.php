<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Request\Factory;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Provider\AddressCoordinatesProviderInerface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Request\GoogleApiRequest;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class DistanceMatrixApiRequestFactory implements GoogleApiRequestFactoryInterface
{
    const ORIGINS = 'origins';
    const DESTINATIONS = 'destinations';
    const MODE = 'mode';
    const UNITS = 'units';

    const METRIC_UNIT = 'metric';
    const IMPERIAL_UNIT = 'imperial';
    
    const MODE_CONFIG_FIELD = 'marello_enterprise_google_api.google_distance_matrix_mode';

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var AddressCoordinatesProviderInerface
     */
    private $coordinatesProvider;

    /**
     * @param ConfigManager $configManager
     * @param AddressCoordinatesProviderInerface $coordinatesProvider
     */
    public function __construct(
        ConfigManager $configManager,
        AddressCoordinatesProviderInerface $coordinatesProvider
    ) {
        $this->configManager = $configManager;
        $this->coordinatesProvider = $coordinatesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(GoogleApiContextInterface $context)
    {
        return new GoogleApiRequest([
            GoogleApiRequest::FIELD_REQUEST_PARAMETERS => [
                self::UNITS => self::METRIC_UNIT,
                self::MODE => $this->configManager->get(self::MODE_CONFIG_FIELD),
                self::ORIGINS => $this->getCoordinates($context->getOriginAddress()),
                self::DESTINATIONS => $this->getCoordinates($context->getDestinationAddress())
            ]
        ]);
    }

    /**
     * @param MarelloAddress $address
     * @return string|null
     * @throws \Exception
     */
    private function getCoordinates(MarelloAddress $address)
    {
        $coordinates = $this->coordinatesProvider->getCoordinates($address);
        if (isset($coordinates[GeocodingApiResultFactory::LATITUDE]) &&
            isset($coordinates[GeocodingApiResultFactory::LONGITUDE])
        ) {
            return sprintf(
                '%s,%s',
                $coordinates[GeocodingApiResultFactory::LATITUDE],
                $coordinates[GeocodingApiResultFactory::LONGITUDE]
            );
        }
        
        return null;
    }
}
