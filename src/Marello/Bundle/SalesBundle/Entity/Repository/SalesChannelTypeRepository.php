<?php

namespace Marello\Bundle\SalesBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannelType;

class SalesChannelTypeRepository extends ServiceEntityRepository
{
    /**
     * @param string $query
     * @return SalesChannelType[]
     */
    public function search($query)
    {
        $qb = $this->createQueryBuilder('sct');
        $qb
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->like('sct.name', ':query'),
                    $qb->expr()->like('sct.label', ':query')
                )
            )
            ->setParameter('query', '%' . $query . '%');

        return $qb->getQuery()->getResult();
    }
}
