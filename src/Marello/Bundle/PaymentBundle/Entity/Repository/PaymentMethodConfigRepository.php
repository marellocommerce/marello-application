<?php

namespace Marello\Bundle\PaymentBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;

class PaymentMethodConfigRepository extends ServiceEntityRepository
{
    /**
     * @param string $method
     */
    public function deleteByMethod($method)
    {
        $qb = $this->createQueryBuilder('methodConfig');

        $qb->delete()
            ->where(
                $qb->expr()->eq('methodConfig.method', ':method')
            )
            ->setParameter('method', $method);

        $qb->getQuery()->execute();
    }

    /**
     * @param array $ids
     */
    public function deleteByIds(array $ids)
    {
        $qb = $this->createQueryBuilder('methodConfig');
        $qb->delete()
            ->where($qb->expr()->in('methodConfig.id', ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()->execute();
    }
    
    /**
     * @param string|string[] $method
     *
     * @return PaymentMethodConfig[]
     */
    public function findByMethod($method)
    {
        return $this->findBy([
            'method' => $method
        ]);
    }
}
