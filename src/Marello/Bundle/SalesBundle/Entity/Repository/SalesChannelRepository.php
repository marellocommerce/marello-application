<?php

namespace Marello\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class SalesChannelRepository extends EntityRepository
{
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

        return $qb->getQuery()->getScalarResult();
    }

    /**
     * Get excluded sales channel ids
     * @param array $relatedChannelIds
     * @return array
     */
    public function findExcludedSalesChannelIds(array $relatedChannelIds)
    {
        return $this->createQueryBuilder('sc')
            ->select('sc.id')
            ->where('sc.id NOT IN(:channels)')
            ->setParameter('channels', $relatedChannelIds)
            ->getQuery()
            ->getArrayResult();
    }
}
