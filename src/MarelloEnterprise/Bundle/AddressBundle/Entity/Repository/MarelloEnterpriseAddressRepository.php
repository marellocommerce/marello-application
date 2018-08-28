<?php

namespace MarelloEnterprise\Bundle\AddressBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use MarelloEnterprise\Bundle\AddressBundle\Entity\MarelloEnterpriseAddress;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class MarelloEnterpriseAddressRepository extends EntityRepository
{
    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @param AclHelper $aclHelper
     */
    public function setAclHelper(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }
    
    /**
     * @param MarelloAddress $address
     *
     * @return MarelloEnterpriseAddress[]
     */
    public function findByAddressParts(MarelloAddress $address)
    {
        $qb = $this->createQueryBuilder('eeaddr');
        $qb
            ->innerJoin('eeaddr.address', 'addr')
            ->innerJoin('addr.country', 'country')
            ->andWhere('eeaddr.latitude IS NOT NULL')
            ->andWhere('eeaddr.longitude IS NOT NULL')
            ->andWhere($qb->expr()->eq('country.iso2Code', ':countryIso2'))
            ->andWhere($qb->expr()->eq('addr.city', ':city'))
            ->andWhere($qb->expr()->eq('addr.street', ':street'))
            ->setParameter('countryIso2', $address->getCountryIso2())
            ->setParameter('city', $address->getCity())
            ->setParameter('street', $address->getStreet());

        if ($address->getRegion() === null) {
            $qb->andWhere('addr.region IS NULL');
        } else {
            $qb
                ->innerJoin('addr.region', 'region')
                ->andWhere($qb->expr()->eq('region.code', ':regionCode'))
                ->setParameter('regionCode', $address->getRegionCode());
        }
        if ($address->getRegionText() === null) {
            $qb->andWhere('addr.regionText IS NULL');
        } else {
            $qb
                ->andWhere($qb->expr()->eq('addr.regionText', ':regionText'))
                ->setParameter('regionText', $address->getRegionText());
        }
        if ($address->getStreet2() === null) {
            $qb->andWhere('addr.street2 IS NULL');
        } else {
            $qb
                ->andWhere($qb->expr()->eq('addr.street2', ':street2'))
                ->setParameter('street2', $address->getStreet2());
        }
        if ($address->getPostalCode() === null) {
            $qb->andWhere('addr.postalCode IS NULL');
        } else {
            $qb
                ->andWhere($qb->expr()->eq('addr.postalCode', ':postalCode'))
                ->setParameter('postalCode', $address->getPostalCode());
        }

        return $this->aclHelper->apply($qb)->getResult();
    }
}
