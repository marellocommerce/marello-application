<?php

namespace Marello\Bundle\Magento2Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\Magento2Bundle\Model\SalesChannelInfo;

class WebsiteRepository extends EntityRepository
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
            ])
            ->innerJoin('m2w.salesChannel', 'salesChannel')
            ->innerJoin('m2w.channel', 'channel');

        $result = $qb->getQuery()->getResult();

        $returnResult = [];
        foreach ($result as $resultItem) {
            $returnResult[$resultItem['salesChannelId']] = new SalesChannelInfo(
                $resultItem['websiteId'],
                $resultItem['integrationChannelId'],
                $resultItem['salesChannelActive'],
                $resultItem['integrationActive']
            );
        }

        return $returnResult;
    }
}
