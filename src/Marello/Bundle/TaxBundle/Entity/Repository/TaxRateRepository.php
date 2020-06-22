<?php

namespace Marello\Bundle\TaxBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\TaxBundle\Entity\TaxRate;

class TaxRateRepository extends EntityRepository
{
    /**
     * @param string $key
     * @return TaxRate[]
     */
    public function findByDataKey($key)
    {
        $qb = $this->createQueryBuilder('taxRate');
        $qb
            ->where($qb->expr()->like('taxRate.data', $qb->expr()->literal("%$key%")));

        $results = $qb->getQuery()->getResult();

        return $results;
    }
}
