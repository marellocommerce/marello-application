<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class WarehouseChannelGroupLinkRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        private AclHelper $aclHelper
    ) {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return false|mixed
     */
    public function findSystemLink()
    {
        $qb = $this->createQueryBuilder('wcgl');
        $qb
            ->where($qb->expr()->eq('wcgl.system', $qb->expr()->literal(true)));
        $results = $this->aclHelper->apply($qb)->getResult();

        return reset($results);
    }

    /**
     * @param SalesChannelGroup $group
     * @return false|mixed
     */
    public function findLinkBySalesChannelGroup(SalesChannelGroup $group)
    {
        $qb = $this->createQueryBuilder('wcgl');
        $qb
            ->join('wcgl.salesChannelGroups', 'scg')
            ->andWhere($qb->expr()->eq('scg.id', ':group_id'))
            ->setParameter('group_id', $group->getId());
        $results = $this->aclHelper->apply($qb)->getResult();

        return reset($results);
    }
}
