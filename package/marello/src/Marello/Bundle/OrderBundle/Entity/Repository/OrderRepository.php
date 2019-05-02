<?php

namespace Marello\Bundle\OrderBundle\Entity\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OrderRepository extends EntityRepository
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
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return int
     */
    public function getTotalRevenueValue(\DateTime $start, \DateTime $end)
    {
        $select = 'SUM(
             CASE WHEN orders.grandTotal IS NOT NULL THEN orders.grandTotal ELSE 0 END
             ) as val';
        $qb     = $this->createQueryBuilder('orders');
        $qb->select($select)
            ->andWhere($qb->expr()->between('orders.createdAt', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $start)
            ->setParameter('dateEnd', $end);

        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ?: 0;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return int
     */
    public function getTotalOrdersNumberValue(\DateTime $start, \DateTime $end)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('count(o.id) as val')
            ->andWhere($qb->expr()->between('o.createdAt', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $start)
            ->setParameter('dateEnd', $end);

        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ?: 0;
    }

    /**
     * get Average Order Amount by given period
     *
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return int
     */
    public function getAverageOrderValue(\DateTime $start, \DateTime $end)
    {
        $select = 'SUM(
             CASE WHEN o.grandTotal IS NOT NULL THEN o.grandTotal ELSE 0 END
             ) as revenue,
             count(o.id) as ordersCount';
        $qb     = $this->createQueryBuilder('o');
        $qb->select($select)
            ->andWhere($qb->expr()->between('o.createdAt', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $start)
            ->setParameter('dateEnd', $end);

        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['revenue'] ? $value['revenue'] / $value['ordersCount'] : 0;
    }

    /**
     * @return array
     */
    public function getOrdersCurrencies()
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->distinct(true)
            ->select('o.currency');

        return $this->aclHelper->apply($qb)->getArrayResult();
    }
}
