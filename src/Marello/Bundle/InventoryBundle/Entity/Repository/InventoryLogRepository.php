<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Marello\Bundle\ProductBundle\Entity\Product;

class InventoryLogRepository extends EntityRepository
{

    /**
     * Returns a sequence of records containing values representing how much were respective quantities changed on each
     * day between given from and to values.
     *
     * @param Product   $product
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return array
     */
    public function getQuantitiesForProduct(Product $product, \DateTime $from, \DateTime $to)
    {
        $qb = $this->createQueryBuilder('l');

        /*
         * Select sums of changes and group them by date.
         */
        $qb
            ->select(
                'SUM(l.newQuantity - l.oldQuantity) AS quantity',
                'SUM(l.newAllocatedQuantity - l.oldAllocatedQuantity) AS allocatedQuantity',
                'DATE(l.createdAt) AS date'
            )
            ->join('l.inventoryItem', 'i')
            ->andWhere($qb->expr()->eq('IDENTITY(i.product)', ':product'))
            ->andWhere($qb->expr()->between('l.createdAt', ':from', ':to'))
            ->groupBy('date');

        $qb->setParameters(compact('product', 'from', 'to'));

        $results = $qb
            ->getQuery()
            ->getArrayResult();

        return $results;
    }

    /**
     * Returns initial quantities for given day. Quantity values at the start of the day.
     * This is either old value of first record of the day, or new value of last record before the day.
     * In case no record is preset, bot quantities are returned as zeroes.
     *
     * @param Product   $product
     * @param \DateTime $at
     *
     * @return array
     */
    public function getInitialQuantities(Product $product, \DateTime $at)
    {
        /*
         * First. Find first record on same day.
         */

        $qb = $this->createQueryBuilder('l');

        $qb
            ->select('l.oldQuantity AS quantity', 'l.oldAllocatedQuantity AS allocatedQuantity')
            ->join('l.inventoryItem', 'i')
            ->andWhere($qb->expr()->eq('IDENTITY(i.product)', ':product'))
            ->andWhere($qb->expr()->eq('DATE(l.createdAt)', 'DATE(:at)'))
            ->orderBy('l.createdAt', 'ASC');

        $qb
            ->setParameters(compact('product', 'at'));

        $result = $qb
            ->getQuery()
            ->setMaxResults(1)
            ->getArrayResult();

        if (!empty($result)) {
            return $result[0];
        }

        /*
         * Second. Find last record before.
         */

        $qb = $this->createQueryBuilder('l');

        $qb
            ->select('l.newQuantity AS quantity', 'l.newAllocatedQuantity AS allocatedQuantity')
            ->join('l.inventoryItem', 'i')
            ->andWhere($qb->expr()->eq('IDENTITY(i.product)', ':product'))
            ->andWhere($qb->expr()->lt('l.createdAt', ':at'))
            ->orderBy('l.createdAt', 'DESC');

        $qb
            ->setParameters(compact('product', 'at'));

        $result = $qb
            ->getQuery()
            ->setMaxResults(1)
            ->getArrayResult();

        if (!empty($result)) {
            return $result[0];
        }

        /*
         * Third. No record, so sequence starts at zero.
         */

        return [
            'quantity'          => 0,
            'allocatedQuantity' => 0,
        ];
    }
}
