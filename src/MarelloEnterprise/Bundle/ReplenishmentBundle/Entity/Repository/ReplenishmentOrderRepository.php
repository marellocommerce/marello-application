<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ReplenishmentOrderRepository extends EntityRepository
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

        return $this->aclHelper->apply($qb->getQuery())->getResult();
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

        return $this->aclHelper->apply($qb->getQuery())->getResult();
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

        return $this->aclHelper->apply($qb->getQuery())->getResult();
    }
}
