<?php

namespace Marello\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class SalesChannelRepository extends EntityRepository
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
     * Return product prices for specified channel and productId
     *
     * @param int $salesChannel
     * @param int $productId
     *
     * @return ProductChannelPrice[]
     */
    public function findOneBySalesChannel($salesChannel, $productId)
    {
        $qb = $this->createQueryBuilder('price');
        $qb
            ->where(
                $qb->expr()->eq('IDENTITY(price.channel)', ':salesChannel'),
                $qb->expr()->eq('IDENTITY(price.product)', ':productId')
            )
            ->setParameter('salesChannel', $salesChannel)
            ->setParameter('productId', $productId);

        return $this->aclHelper->apply($qb)->getScalarResult();
    }

    /**
     * Get excluded sales channel ids
     * @param array $relatedChannelIds
     * @return array
     */
    public function findExcludedSalesChannelIds(array $relatedChannelIds)
    {
        $qb = $this->createQueryBuilder('sc')
            ->select('sc.id')
            ->where('sc.id NOT IN(:channels)')
            ->setParameter('channels', $relatedChannelIds);

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @return QueryBuilder
     */
    private function getActiveChannelsQuery()
    {
        $qb = $this->createQueryBuilder('sc');
        
        return $qb
            ->where($qb->expr()->eq('sc.active', $qb->expr()->literal(true)))
            ->orderBy('sc.name', 'ASC');
    }

    /**
     * Get active channels.
     * @return SalesChannel[]
     */
    public function getActiveChannels()
    {
        $qb = $this->getActiveChannelsQuery();

        return $this->aclHelper->apply($qb)->getResult();
    }

    /**
     * Get default active channels.
     * @return SalesChannel[]
     */
    public function getDefaultActiveChannels()
    {
        $qb = $this->getActiveChannelsQuery();
        $qb->andWhere($qb->expr()->eq('sc.default', $qb->expr()->literal(true)));

        return $this->aclHelper->apply($qb)->getResult();
    }
}
