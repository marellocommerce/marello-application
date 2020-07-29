<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ProductTaxClassRepository extends EntityRepository implements NotInOriginIdsInterface
{
    /**
     * {@inheritDoc}
     */
    public function getEntitiesNotInOriginIdsInGivenIntegration(
        array $existedRecordsOriginIds,
        int $integrationId
    ): array {
        $qb = $this->createQueryBuilder('m2pt');
        $qb
            ->select('m2pt')
            ->where($qb->expr()->notIn('m2pt.originId', ':existedRecordsOriginIds'))
            ->andWhere($qb->expr()->eq('m2pt.channel', ':integrationId'))
            ->setParameter('existedRecordsOriginIds', $existedRecordsOriginIds)
            ->setParameter('integrationId', $integrationId);

        return $qb->getQuery()->getResult();
    }
}
