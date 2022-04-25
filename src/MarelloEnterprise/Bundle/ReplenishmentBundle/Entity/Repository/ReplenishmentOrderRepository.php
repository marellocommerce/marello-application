<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query\Expr\Join;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;

class ReplenishmentOrderRepository extends ServiceEntityRepository
{
    /**
     * @param int $replOrderConfig
     *
     * @return ReplenishmentOrder[]
     */
    public function findByConfig($replOrderConfig)
    {
        $qb = $this->createQueryBuilder('ro');
        $qb
            ->where(
                $qb->expr()->eq(':replOrderConfig', 'ro.replOrderConfig')
            )
            ->setParameter('replOrderConfig', $replOrderConfig);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $productSku
     *
     * @return ReplenishmentOrder[]
     */
    public function findByProductSku($productSku)
    {
        $qb = $this->createQueryBuilder('ro');
        $qb
            ->innerJoin('ro.replOrderItems', 'roi', Join::WITH, 'roi.productSku = :sku')
            ->setParameter('sku', $productSku);

        return $qb->getQuery()->getResult();
    }
    
    /**
     * @param \DateTime $datetime
     *
     * @return ReplenishmentOrder[]
     */
    public function findNotAllocated(\DateTime $datetime = null)
    {
        if (!$datetime) {
            $datetime = new \DateTime();
        }

        $qb = $this->createQueryBuilder('ro');
        $qb
            ->innerJoin('ro.replOrderItems', 'roi', Join::WITH, 'roi.inventoryQty IS NULL')
            ->andWhere($qb->expr()->gte(':dt', 'ro.executionDateTime'))
            ->setParameter('dt', $datetime, Type::DATETIME);

        return $qb->getQuery()->getResult();
    }
}
