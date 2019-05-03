<?php

namespace Marello\Bundle\TaxBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\TaxBundle\Entity\TaxCode;

class TaxJurisdictionRepository extends EntityRepository
{
    /**
     * @param string $key
     * @return TaxCode[]
     */
    public function findByDataKey($key)
    {
        $qb = $this->createQueryBuilder('taxJurisdiction');
        $qb
            ->where($qb->expr()->like('taxJurisdiction.data', $qb->expr()->literal("%$key%")));

        $results = $qb->getQuery()->getResult();

        return $results;
    }
}
