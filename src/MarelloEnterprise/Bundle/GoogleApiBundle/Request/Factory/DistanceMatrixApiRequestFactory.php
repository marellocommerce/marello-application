<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Request\Factory;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\GoogleApiContextInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Request\GoogleApiRequest;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class DistanceMatrixApiRequestFactory implements GoogleApiRequestFactoryInterface
{
    const ORIGINS = 'origins';
    const DESTINATIONS = 'destinations';
    const MODE = 'mode';
    const UNITS = 'units';

    const METRIC_UNIT = 'metric';
    const IMPERIAL_UNIT = 'imperial';

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ConfigManager $configManager
     */
    public function __construct(DoctrineHelper $doctrineHelper, ConfigManager $configManager)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->configManager = $configManager;
    }

    public function createRequest(GoogleApiContextInterface $context)
    {
        return new GoogleApiRequest([
            GoogleApiRequest::FIELD_REQUEST_PARAMETERS => [
                self::UNITS => self::METRIC_UNIT,
                self::MODE => $this->configManager->get('marello_enterprise_google_api.google_distance_matrix_mode'),
                self::ORIGINS => $this->getCoordinates($context->getOriginAddress()),
                self::DESTINATIONS => $this->getCoordinates($context->getDestinationAddress())
            ]
        ]);
    }

    private function getCoordinates(MarelloAddress $address)
    {
        $eeAddress = $this->doctrineHelper
            ->getEntityManagerForClass(MarelloEnterpriseAddress::class)
            ->getRepository(MarelloEnterpriseAddress::class)
            ->findOneBy(['address' => $address]);
        if ($eeAddress && $eeAddress->getLatitude() && $eeAddress->getLongitude()) {
            return sprintf('%s,%s', $eeAddress->getLatitude(), $eeAddress->getLongitude());
        }

        throw new \Exception(sprintf('No coordinates found for "%s"', $address));
    }
}
