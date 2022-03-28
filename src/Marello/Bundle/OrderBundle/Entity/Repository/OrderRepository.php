<?php

namespace Marello\Bundle\OrderBundle\Entity\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
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
    public function setAclHelper(AclHelper $aclHelper) // weedizp3
    {
        $this->aclHelper = $aclHelper;
    }
    
    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @param SalesChannel|null $salesChannel
     *
     * @return int
     */
    public function getTotalRevenueValue(
        \DateTime $start = null,
        \DateTime $end = null,
        SalesChannel $salesChannel = null
    ) {
        $select = 'SUM(
             CASE WHEN orders.grandTotal IS NOT NULL THEN orders.grandTotal ELSE 0 END
             ) as val';
        $qb     = $this->createQueryBuilder('orders');
        $qb->select($select);
        if ($start && $end) {
            $qb
                ->andWhere($qb->expr()->between('orders.createdAt', ':dateStart', ':dateEnd'))
                ->setParameter('dateStart', $start)
                ->setParameter('dateEnd', $end);
        } elseif ($start) {
            $qb
                ->andWhere($qb->expr()->gte('orders.createdAt', ':dateStart'))
                ->setParameter('dateStart', $start);
        } elseif ($end) {
            $qb
                ->andWhere($qb->expr()->lt('orders.createdAt', ':dateEnd'))
                ->setParameter('dateEnd', $end);
        }
        if ($salesChannel) {
            $qb
                ->andWhere('orders.salesChannelName = :salesChannelName')
                ->setParameter('salesChannelName', $salesChannel->getName());
        }
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ?: 0;
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @param SalesChannel|null $salesChannel
     *
     * @return int
     */
    public function getTotalOrdersNumberValue(
        \DateTime $start = null,
        \DateTime $end = null,
        SalesChannel $salesChannel = null
    ) {
        $qb = $this->createQueryBuilder('o');
        $qb->select('count(o.id) as val');
        if ($start && $end) {
            $qb->andWhere($qb->expr()->between('o.createdAt', ':dateStart', ':dateEnd'))
                ->setParameter('dateStart', $start)
                ->setParameter('dateEnd', $end);
        } elseif ($start) {
            $qb
                ->andWhere($qb->expr()->gte('o.createdAt', ':dateStart'))
                ->setParameter('dateStart', $start);
        } elseif ($end) {
            $qb
                ->andWhere($qb->expr()->lt('o.createdAt', ':dateEnd'))
                ->setParameter('dateEnd', $end);
        }
        if ($salesChannel) {
            $qb
                ->andWhere('o.salesChannelName = :salesChannelName')
                ->setParameter('salesChannelName', $salesChannel->getName());
        }
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ?: 0;
    }

    /**
     * get Average Order Amount by given period
     *
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @param SalesChannel|null $salesChannel
     *
     * @return int
     */
    public function getAverageOrderValue(
        \DateTime $start = null,
        \DateTime $end = null,
        SalesChannel $salesChannel = null
    ) {
        $select = 'SUM(
             CASE WHEN o.grandTotal IS NOT NULL THEN o.grandTotal ELSE 0 END
             ) as revenue,
             count(o.id) as ordersCount';
        $qb     = $this->createQueryBuilder('o');
        $qb->select($select);
        if ($start && $end) {
            $qb
                ->andWhere($qb->expr()->between('o.createdAt', ':dateStart', ':dateEnd'))
                ->setParameter('dateStart', $start)
                ->setParameter('dateEnd', $end);
        } elseif ($start) {
            $qb
                ->andWhere($qb->expr()->gte('o.createdAt', ':dateStart'))
                ->setParameter('dateStart', $start);
        } elseif ($end) {
            $qb
                ->andWhere($qb->expr()->lt('o.createdAt', ':dateEnd'))
                ->setParameter('dateEnd', $end);
        }
        if ($salesChannel) {
            $qb
                ->andWhere('o.salesChannelName = :salesChannelName')
                ->setParameter('salesChannelName', $salesChannel->getName());
        }
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
