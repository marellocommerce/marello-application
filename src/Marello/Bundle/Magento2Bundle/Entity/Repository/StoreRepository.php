<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class StoreRepository extends EntityRepository implements NotInOriginIdsInterface
{
    /**
     * @param int $websiteId
     * @return array
     */
    public function getOriginStoreIdsByWebsiteId(int $websiteId): array
    {
        $qb = $this->createQueryBuilder('m2s');
        $qb
            ->select('m2s.originId')
            ->where($qb->expr()->eq('m2s.website', ':websiteId'))
            ->setParameter('websiteId', $websiteId);

        $result = $qb->getQuery()->getArrayResult();

        return \array_column($result, 'originId');
    }

    /**
     * {@inheritDoc}
     */
    public function getEntitiesNotInOriginIdsInGivenIntegration(
        array $existedRecordsOriginIds,
        int $integrationId
    ): array {
        $qb = $this->createQueryBuilder('m2s');
        $qb
            ->select('m2s')
            ->where($qb->expr()->notIn('m2s.originId', ':existedRecordsOriginIds'))
            ->andWhere($qb->expr()->eq('m2s.channel', ':integrationId'))
            ->setParameter('existedRecordsOriginIds', $existedRecordsOriginIds)
            ->setParameter('integrationId', $integrationId);

        return $qb->getQuery()->getResult();
    }
}
