<?php

namespace Marello\Bundle\PurchaseOrderBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\ProductBundle\Entity\Product;

class PurchaseOrderItemRepository extends EntityRepository
{
    public function getNotCompletedItemsByProduct(Product $product): iterable
    {
        $qb = $this->createQueryBuilder('poi');

        return $qb
            ->where($qb->expr()->andX(
                $qb->expr()->eq('poi.product', ':product'),
                $qb->expr()->neq('poi.status', ':completed')
            ))
            ->setParameter('product', $product)
            ->setParameter('completed', 'completed')
            ->getQuery()->getResult();
    }
}
