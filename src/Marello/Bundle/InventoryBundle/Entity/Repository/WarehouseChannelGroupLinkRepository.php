<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class WarehouseChannelGroupLinkRepository extends ServiceEntityRepository
{
    public function findSystemLink(AclHelper $aclHelper)
    {
        $qb = $this->createQueryBuilder('wcgl');
        $qb
            ->where($qb->expr()->eq('wcgl.system', $qb->expr()->literal(true)));
        $results = $aclHelper->apply($qb)->getResult();

        return reset($results);
    }

    public function findLinkBySalesChannelGroup(
        SalesChannelGroup $group,
        AclHelper $aclHelper
    ) {
        $qb = $this->createQueryBuilder('wcgl');
        $qb
            ->join('wcgl.salesChannelGroups', 'scg')
            ->andWhere($qb->expr()->eq('scg.id', ':group_id'))
            ->setParameter('group_id', $group->getId());
        $results = $aclHelper->apply($qb)->getResult();

        return reset($results);
    }
}
