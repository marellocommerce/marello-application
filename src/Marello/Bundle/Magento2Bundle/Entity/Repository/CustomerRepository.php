<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\CustomerBundle\Entity\Customer as InnerCustomer;

class CustomerRepository extends EntityRepository
{
    /**
     * @param string $hashId
     * @param InnerCustomer $innerCustomer
     * @return mixed
     */
    public function updateHashIdByInnerCustomer(string $hashId, InnerCustomer $innerCustomer)
    {
        $qb = $this->createQueryBuilder('m2c');
        $qb
            ->update()
            ->set('m2c.hashId', ':hashId')
            ->where($qb->expr()->eq('m2c.innerCustomer', ':innerCustomer'));

        $qb
            ->setParameter('hashId', $hashId)
            ->setParameter('innerCustomer', $innerCustomer);

        return $qb->getQuery()->execute();
    }

    /**
     * @param int $customerId
     * @return string|null
     */
    public function getOriginIdByCustomerId(int $customerId): ?string
    {
        $qb = $this->createQueryBuilder('m2c');
        $qb
            ->select('m2c.originId')
            ->where($qb->expr()->eq('m2c.id', ':id'))
            ->setParameter('id', $customerId);

        $qb
            ->setFirstResult(0)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result['originId'] ?? null;
    }
}
