<?php

namespace Marello\Bundle\SalesBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\IntegrationBundle\Entity\Channel as IntegrationChannel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class SalesChannelGroupRepository extends ServiceEntityRepository
{
    public function findSystemChannelGroup(AclHelper $aclHelper): ?SalesChannelGroup
    {
        $qb = $this->createQueryBuilder('scg');
        $qb
            ->where($qb->expr()->eq('scg.system', $qb->expr()->literal(true)));
        $result = $aclHelper->apply($qb)->getResult();
        
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

    /**
     * @param int $salesChannelGroupId
     * @return bool
     */
    public function hasAttachedIntegration(int $salesChannelGroupId): bool
    {
        $qb = $this->createQueryBuilder('scg');
        $qb
            ->select('1')
            ->where($qb->expr()->eq('scg.id', ':salesChannelGroupId'))
            ->andWhere($qb->expr()->isNotNull('scg.integrationChannel'))
            ->setParameter('salesChannelGroupId', $salesChannelGroupId);

        return (bool) $qb->getQuery()->getResult();
    }

    /**
     * @param string $searchTerm
     * @return QueryBuilder
     */
    public function getNonSystemSalesChannelBySearchTermQB(string $searchTerm): QueryBuilder
    {
        $qb = $this->createQueryBuilder('scg');
        $qb
            ->where($qb->expr()->like('LOWER(scg.name)', ':searchTerm'))
            ->andWhere(($qb->expr()->eq('scg.system', $qb->expr()->literal(false))))
            ->setParameter('searchTerm', '%' . mb_strtolower($searchTerm) . '%')
            ->orderBy('scg.name', 'ASC');

        return $qb;
    }
}
