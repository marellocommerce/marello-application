<?php

namespace Marello\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

use Marello\Bundle\PricingBundle\Entity\Product;

class ProductRepository extends EntityRepository
{
    /**
     * Return products for specified price list and product IDs
     *
     * @param int $salesChannel
     * @param array $productIds
     *
     * @return Product[]
     */
    public function findBySalesChannel(
        $salesChannel,
        array $productIds
    ) {
        if (!$productIds) {
            return [];
        }

        $qb = $this->createQueryBuilder('product');
        $qb
            ->where(
                $qb->expr()->isMemberOf(':salesChannel', 'product.channels'),
                $qb->expr()->in('product.id', ':productIds')
            )
            ->setParameter('salesChannel', $salesChannel)
            ->setParameter('productIds', $productIds);

        return $qb->getQuery()->getResult();
    }
}
