<?php

namespace Marello\Bundle\OrderBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OrderItemRepository extends EntityRepository
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
     * @param int $quantity
     * @param string $currency
     *
     * @return array
     */
    public function getTopProductsByRevenue($quantity, $currency)
    {
        $select = 'p.id as id, oi.productSku as sku, oi.productName as name,
                SUM(oi.quantity * oi.price) as revenue, o.currency as currency';

        $qb     = $this->createQueryBuilder('oi');
        $qb
            ->select($select)
            ->innerJoin('oi.order', 'o')
            ->innerJoin('oi.product', 'p')
            ->groupBy('p.id, oi.productSku, oi.productName, o.currency')
            ->orderBy('currency, revenue', 'DESC')
            ->where('o.currency = :currency')
            ->setParameter('currency', $currency)
            ->setMaxResults($quantity);

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param int $quantity
     *
     * @return array
     */
    public function getTopProductsByItemsSold($quantity)
    {
        $select = 'p.id as id, oi.productSku as sku, oi.productName as name, SUM(oi.quantity) as quantity';
        $qb     = $this->createQueryBuilder('oi');
        $qb
            ->select($select)
            ->innerJoin('oi.product', 'p')
            ->groupBy('p.id, oi.productSku, oi.productName')
            ->orderBy('quantity', 'DESC')
            ->setMaxResults($quantity);

        return $this->aclHelper->apply($qb)->getArrayResult();
    }
}
