<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;

class WebsiteRepository extends EntityRepository implements NotInOriginIdsInterface
{
    /**
     * @return SalesChannelInfo[]
     * [
     *    'sales_channel_id' => SalesChannelInfo <SalesChannelInfo>
     * ]
     */
    public function getSalesChannelInfoArray(): array
    {
        $qb = $this->createQueryBuilder('m2w');
        $qb
            ->select([
                'm2w.id as websiteId',
                'salesChannel.id as salesChannelId',
                'channel.id as integrationChannelId',
                'salesChannel.active as salesChannelActive',
                'channel.enabled as integrationActive',
                'salesChannel.currency as salesChannelCurrency',
            ])
            ->innerJoin('m2w.salesChannel', 'salesChannel')
            ->innerJoin('m2w.channel', 'channel');

        $result = $qb->getQuery()->getResult();

        $returnResult = [];
        foreach ($result as $resultItem) {
            $returnResult[$resultItem['salesChannelId']] = new SalesChannelInfo(
                $resultItem['salesChannelId'],
                $resultItem['websiteId'],
                $resultItem['integrationChannelId'],
                $resultItem['salesChannelActive'],
                $resultItem['integrationActive'],
                $resultItem['salesChannelCurrency']
            );
        }

        return $returnResult;
    }

    /**
     * @param int $integrationId
     * @return int[]
     */
    public function getWebsitesIdsByIntegrationId(int $integrationId): array
    {
        $qb = $this->createQueryBuilder('m2w');
        $qb
            ->select('m2w.id')
            ->where($qb->expr()->eq('m2w.channel', ':integrationId'))
            ->setParameter('integrationId', $integrationId);

        $result = $qb->getQuery()->getArrayResult();

        return \array_column($result, 'id');
    }

    /**
     * {@inheritDoc}
     */
    public function getEntitiesNotInOriginIdsInGivenIntegration(
        array $existedRecordsOriginIds,
        int $integrationId
    ): array {
        $qb = $this->createQueryBuilder('m2w');
        $qb
            ->select('m2w')
            ->where($qb->expr()->notIn('m2w.originId', ':existedRecordsOriginIds'))
            ->andWhere($qb->expr()->eq('m2w.channel', ':integrationId'))
            ->setParameter('existedRecordsOriginIds', $existedRecordsOriginIds)
            ->setParameter('integrationId', $integrationId)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $integrationId
     * @return array
     */
    public function getOriginIdsByIntegrationId(int $integrationId): array
    {
        $qb = $this->createQueryBuilder('m2w');
        $qb
            ->select('m2w.originId')
            ->where($qb->expr()->eq('m2w.channel', ':integrationId'))
            ->setParameter('integrationId', $integrationId);

        $result = $qb->getQuery()->getArrayResult();

        return \array_column($result, 'originId');
    }
}
