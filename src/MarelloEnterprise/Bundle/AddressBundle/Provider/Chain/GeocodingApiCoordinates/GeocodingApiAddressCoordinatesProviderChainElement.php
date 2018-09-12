<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\GeocodingApiCoordinates;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\Repository\MarelloEnterpriseAddressRepository;
use MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\AbstractAddressCoordinatesProviderChainElement;
use MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\AddressCoordinatesProviderChainElementInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Context\Factory\GoogleApiContextFactory;
use MarelloEnterprise\Bundle\GoogleApiBundle\Exception\GoogleApiException;
use MarelloEnterprise\Bundle\GoogleApiBundle\Provider\GoogleApiResultsProviderInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class GeocodingApiAddressCoordinatesProviderChainElement extends AbstractAddressCoordinatesProviderChainElement implements
    AddressCoordinatesProviderChainElementInterface
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var GoogleApiResultsProviderInterface
     */
    private $geocodingApiResultsProvider;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param GoogleApiResultsProviderInterface $geocodingApiResultsProvider
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        GoogleApiResultsProviderInterface $geocodingApiResultsProvider
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->geocodingApiResultsProvider = $geocodingApiResultsProvider;
    }

    /**
     * @inheritDoc
     */
    public function collectCoordinates(MarelloAddress $address)
    {
        $em = $this->doctrineHelper->getEntityManagerForClass(MarelloEnterpriseAddress::class);
        /** @var MarelloEnterpriseAddressRepository $repository */
        $repository = $this->doctrineHelper
            ->getEntityManagerForClass(MarelloEnterpriseAddress::class)
            ->getRepository(MarelloEnterpriseAddress::class);
        /** @var MarelloAddress $sameAddresses */
        $sameAddresses = $repository->findByAddressParts($address);
        if (!$address->getId()) {
            $sameAddresses[] = $address;
        }
        $results = $this->geocodingApiResultsProvider
            ->getApiResults(GoogleApiContextFactory::createContext($address));
        if ($results->getStatus() === false && $results->getErrorType() && $results->getErrorMessage()) {
            throw new GoogleApiException($results->getErrorMessage());
        } else {
            $latitude = $results->getResult()[GeocodingApiResultFactory::LATITUDE];
            $longitude = $results->getResult()[GeocodingApiResultFactory::LONGITUDE];
            foreach ($sameAddresses as $address) {
                $eeAddress = new MarelloEnterpriseAddress();
                $eeAddress
                    ->setAddress($address)
                    ->setLatitude($latitude)
                    ->setLongitude($longitude);

                $em->persist($eeAddress);
            }
            $em->flush();

            return [
                GeocodingApiResultFactory::LATITUDE => $latitude,
                GeocodingApiResultFactory::LONGITUDE => $longitude,
            ];
        }
    }
}
