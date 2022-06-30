<?php

namespace Marello\Bundle\UPSBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Oro\Bundle\AddressBundle\Entity\Country;
use Marello\Bundle\UPSBundle\Entity\ShippingService;

class ShippingServiceRepository extends ServiceEntityRepository
{
    /**
     * @param Country $country
     * @return ShippingService[]
     */
    public function getShippingServicesByCountry(Country $country)
    {
        return $this
            ->createQueryBuilder('shippingService')
            ->andWhere('shippingService.country = :country')
            ->orderBy('shippingService.description')
            ->setParameter(':country', $country->getIso2Code())
            ->getQuery()
            ->getResult();
    }
}
