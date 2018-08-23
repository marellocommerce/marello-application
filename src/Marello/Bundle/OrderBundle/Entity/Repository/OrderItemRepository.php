<?php

namespace Marello\Bundle\OrderBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OrderItemRepository extends EntityRepository
{
    /**
     * @var AclHelper
     */
    private $aclHelper;
    
    /**
     * @var DateFilterProcessor
     */
    protected $dateFilterProcessor;

    /**
     * @param AclHelper $aclHelper
     */
    public function setAclHelper(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param DateFilterProcessor $dateFilterProcessor
     */
    public function setDateFilterProcessor(DateFilterProcessor $dateFilterProcessor)
    {
        $this->dateFilterProcessor = $dateFilterProcessor;
    }
    
    /**
     * @param int $quantity
     * @param string $currency
     * @param array $dateRange
     *
     * @return array
     */
    public function getTopProductsByRevenue($quantity, $currency, array $dateRange)
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
        $this->dateFilterProcessor->applyDateRangeFilterToQuery($qb, $dateRange, 'o.createdAt');

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param int $quantity
     * @param array $dateRange
     *
     * @return array
     */
    public function getTopProductsByItemsSold($quantity, array $dateRange)
    {
        $select = 'p.id as id, oi.productSku as sku, oi.productName as name, SUM(oi.quantity) as quantity';
        $qb     = $this->createQueryBuilder('oi');
        $qb
            ->select($select)
            ->innerJoin('oi.order', 'o')
            ->innerJoin('oi.product', 'p')
            ->groupBy('p.id, oi.productSku, oi.productName')
            ->orderBy('quantity', 'DESC')
            ->setMaxResults($quantity);
        $this->dateFilterProcessor->applyDateRangeFilterToQuery($qb, $dateRange, 'o.createdAt');

        return $this->aclHelper->apply($qb)->getArrayResult();
    }
}
