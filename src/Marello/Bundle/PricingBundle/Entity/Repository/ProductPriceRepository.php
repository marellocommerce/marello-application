<?php

namespace Marello\Bundle\PricingBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

use Marello\Bundle\PricingBundle\Entity\ProductPrice;

class ProductPriceRepository extends EntityRepository
{
    /**
     * Return product prices for specified price list and product IDs
     *
     * @param int $salesChannel
     * @param array $productIds
     *
     * @return ProductPrice[]
     */
    public function findBySalesChannel(
        $salesChannel,
        array $productIds
    ) {
        if (!$productIds) {
            return [];
        }

        $qb = $this->createQueryBuilder('price');
        $qb
            ->where(
                $qb->expr()->eq('IDENTITY(price.channel)', ':salesChannel'),
                $qb->expr()->in('IDENTITY(price.product)', ':productIds')
            )
            ->setParameter('salesChannel', $salesChannel)
            ->setParameter('productIds', $productIds);

        return $qb->getQuery()->getResult();
    }
}
