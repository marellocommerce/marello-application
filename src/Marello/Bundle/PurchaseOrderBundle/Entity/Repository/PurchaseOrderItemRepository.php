<?php

namespace Marello\Bundle\PurchaseOrderBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class PurchaseOrderItemRepository extends EntityRepository
{
    public function getExpectedItemsByProduct(Product $product): iterable
    {
        $qb = $this->createQueryBuilder('poi');

        return $qb
            ->where($qb->expr()->andX(
                $qb->expr()->eq('poi.product', ':product'),
                $qb->expr()->eq('poi.status', ':pendingStatus')
            ))
            ->setParameter('product', $product)
            ->setParameter('pendingStatus', PurchaseOrderItem::STATUS_PENDING)
            ->getQuery()->getResult();
    }
}
