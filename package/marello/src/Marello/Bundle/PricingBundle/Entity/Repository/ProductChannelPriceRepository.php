<?php

namespace Marello\Bundle\PricingBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;

class ProductChannelPriceRepository extends EntityRepository
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
}
