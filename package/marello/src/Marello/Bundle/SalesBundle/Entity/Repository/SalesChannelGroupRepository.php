<?php

namespace Marello\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\IntegrationBundle\Entity\Channel as IntegrationChannel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class SalesChannelGroupRepository extends EntityRepository
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
     * @return SalesChannelGroup
     */
    public function findSystemChannelGroup()
    {
        $qb = $this->createQueryBuilder('scg');
        $qb
            ->where($qb->expr()->eq('scg.system', $qb->expr()->literal(true)));
        $result = $this->aclHelper->apply($qb)->getResult();
        
        return reset($result);
    }

    /**
     * @param IntegrationChannel $integrationChannel
     * @return SalesChannelGroup|null
     */
    public function findSalesChannelGroupAttachedToIntegration(
        IntegrationChannel $integrationChannel
    ): ?SalesChannelGroup {
        return $this->findOneBy(['integrationChannel' => $integrationChannel]);
    }
}
