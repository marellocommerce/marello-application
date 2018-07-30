<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\StraightLine;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Distance\Chain\Element\AbstractAddressesDistanceCalculatorChainElement;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Psr\Log\LoggerInterface;

class StraightLineAddressesDistanceCalculatorChainElement extends AbstractAddressesDistanceCalculatorChainElement
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param LoggerInterface $logger
     */
    public function __construct(DoctrineHelper $doctrineHelper, LoggerInterface $logger)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->logger = $logger;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getDistance(
        MarelloAddress $originAddress,
        MarelloAddress $destinationAddress,
        $unit = 'metric'
    ) {
        $repository = $this->doctrineHelper
            ->getEntityManagerForClass(MarelloEnterpriseAddress::class)
            ->getRepository(MarelloEnterpriseAddress::class);
        $originGeocodedAddress = $repository->findOneBy(['address' => $originAddress]);
        $destinationGeocodedAddress = $repository->findOneBy(['address' => $destinationAddress]);

        if (!$originGeocodedAddress || $destinationGeocodedAddress) {
            return null;
        }
        
        $coordinatesValid = $this->checkCoordinates([$originGeocodedAddress, $destinationGeocodedAddress]);
        if (!$coordinatesValid) {
            return null;
        }
        
        $lat1 = $originGeocodedAddress->getLatitude();

        $lon1 = $originGeocodedAddress->getLongitude();
        $lat2 = $destinationGeocodedAddress->getLatitude();
        $lon2 = $destinationGeocodedAddress->getLongitude();
        
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
    }
    
    /**
     * @param MarelloEnterpriseAddress[] $eeAddresses
     * @return bool
     */
    protected function checkCoordinates(array $eeAddresses)
    {
        foreach ($eeAddresses as $eeAddress) {
            if (null === $eeAddress || null === $eeAddress->getLatitude() || null === $eeAddress->getLongitude()) {
                $this->logger->error(sprintf('No coordinates found for "%s"', $eeAddress->getAddress()));
                return false;
            }
        }
        
        return true;
    }
}
