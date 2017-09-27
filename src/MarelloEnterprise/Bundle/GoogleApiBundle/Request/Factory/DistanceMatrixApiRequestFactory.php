<?php

namespace MarelloEnterprise\Bundle\GoogleApiBundle\Request\Factory;

use Doctrine\ORM\EntityRepository;
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
    
    const MODE_CONFIG_FIELD = 'marello_enterprise_google_api.google_distance_matrix_mode';

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ConfigManager $configManager
     */
    public function __construct(DoctrineHelper $doctrineHelper, ConfigManager $configManager)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->configManager = $configManager;
        $this->repository = $this->getRepository();
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
     * @return mixed
     * @throws \Exception
     */
    private function getCoordinates(MarelloAddress $address)
    {
        $eeAddress = $this->repository->findOneBy(['address' => $address]);
        if ($eeAddress && null !== $eeAddress->getLatitude() && null !== $eeAddress->getLongitude()) {
            return sprintf('%s,%s', $eeAddress->getLatitude(), $eeAddress->getLongitude());
        }

        throw new \Exception(sprintf('No coordinates found for "%s"', $address));
    }

    /**
     * @return EntityRepository
     */
    private function getRepository()
    {
        return $this->doctrineHelper
            ->getEntityManagerForClass(MarelloEnterpriseAddress::class)
            ->getRepository(MarelloEnterpriseAddress::class);
    }
}
