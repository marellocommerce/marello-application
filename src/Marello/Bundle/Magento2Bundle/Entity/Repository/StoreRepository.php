<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class StoreRepository extends EntityRepository
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
}
