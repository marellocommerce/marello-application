<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class WarehouseChannelGroupLinkRepository extends EntityRepository
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
     * @return WarehouseChannelGroupLink
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
     * @return WarehouseChannelGroupLink
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
