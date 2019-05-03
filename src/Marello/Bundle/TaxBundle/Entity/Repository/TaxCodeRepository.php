<?php

namespace Marello\Bundle\TaxBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\TaxBundle\Entity\TaxCode;

class TaxCodeRepository extends EntityRepository
{
    /**
     * @param string $key
     * @return TaxCode[]
     */
    public function findByDataKey($key)
    {
        $qb = $this->createQueryBuilder('taxRule');
        $qb
            ->where($qb->expr()->like('taxRule.data', $qb->expr()->literal("%$key%")));

        $results = $qb->getQuery()->getResult();

        return $results;
    }
}
