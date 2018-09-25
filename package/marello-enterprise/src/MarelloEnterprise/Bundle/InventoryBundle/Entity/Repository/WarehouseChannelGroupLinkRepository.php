<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\SecurityProBundle\ORM\Walker\AclHelper;

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
     * @return SalesChannelGroup[]
     */
    public function getNotLinkedSalesChannelGroupsGridQueryBuilder()
    {
        $hasLink = <<<HASLINK
(CASE WHEN (:linkOwner IS NOT NULL) THEN
CASE WHEN (scg.id IN (:channelGroups) OR scg.id IN (:data_in)) AND scg.id NOT IN (:data_not_in)
THEN true ELSE false END
ELSE
CASE WHEN scg.id IN (:data_in) AND scg.id NOT IN (:data_not_in)
THEN true ELSE false END
END) as hasLink
HASLINK;
        $subQb = $this->createQueryBuilder('wcgl');
        $qb = $this
            ->getEntityManager()
            ->getRepository('MarelloSalesBundle:SalesChannelGroup')
            ->createQueryBuilder('scg');
        $qb
            ->select(
                'scg',
                $hasLink
            )
            ->andWhere('scg.system != true')
            ->andWhere(
                $qb->expr()->notIn(
                    'scg.id',
                    $subQb
                        ->select('sc.id')
                        ->join('wcgl.salesChannelGroups', 'sc')
                        ->where('wcgl.id != :linkOwner')
                        ->getDQL()
                )
            );

        return $qb;
    }
}
