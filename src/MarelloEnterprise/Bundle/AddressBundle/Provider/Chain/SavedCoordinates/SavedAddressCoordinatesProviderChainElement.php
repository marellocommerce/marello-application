<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\SavedCoordinates;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\Repository\MarelloEnterpriseAddressRepository;
use MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\AbstractAddressCoordinatesProviderChainElement;
use MarelloEnterprise\Bundle\AddressBundle\Provider\Chain\AddressCoordinatesProviderChainElementInterface;
use MarelloEnterprise\Bundle\GoogleApiBundle\Result\Factory\GeocodingApiResultFactory;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class SavedAddressCoordinatesProviderChainElement extends AbstractAddressCoordinatesProviderChainElement implements
    AddressCoordinatesProviderChainElementInterface
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @inheritDoc
     */
    public function collectCoordinates(MarelloAddress $address)
    {
        /** @var MarelloEnterpriseAddressRepository $repository */
        $repository = $this->doctrineHelper
            ->getEntityManagerForClass(MarelloEnterpriseAddress::class)
            ->getRepository(MarelloEnterpriseAddress::class);
        $eeAddresses = $repository->findByAddressParts($address);

        if (!empty($eeAddresses)) {
            /** @var MarelloEnterpriseAddress $eeAddress */
            $eeAddress = reset($eeAddresses);
            
            return [
                GeocodingApiResultFactory::LATITUDE => $eeAddress->getLatitude(),
                GeocodingApiResultFactory::LONGITUDE => $eeAddress->getLongitude(),
            ];
        }
        
        return null;
    }
}
