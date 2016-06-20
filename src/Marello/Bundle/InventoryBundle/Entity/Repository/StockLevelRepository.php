<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\ProductBundle\Entity\Product;

class StockLevelRepository extends EntityRepository
{
    
    /**
     * Returns a set of records containing last stock level on each day in given interval.
     *
     * @param Product   $product
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return array
     */
    public function getStockLevelsForProduct(Product $product, \DateTime $from, \DateTime $to)
    {
        $qb = $this->createQueryBuilder('l');
        $qb
            ->join('l.inventoryItem', 'i')
            ->leftJoin('l.previousLevel', 'p');

        /*
         * Select sums of changes and group them by date.
         */
        $qb
            ->select(
                'SUM(l.stock - COALESCE(p.stock, 0)) AS stock',
                'SUM(l.allocatedStock - COALESCE(p.allocatedStock, 0)) AS allocatedStock',
                'DATE(l.createdAt) AS date'
            )
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
     * Returns initial stock for given day.
     *
     * @param Product   $product
     * @param \DateTime $at
     *
     * @return array
     */
    public function getInitialStock(Product $product, \DateTime $at)
    {
        /*
         * First. Find first record on same day.
         */

        $qb = $this->createQueryBuilder('l');

        $qb
            ->leftJoin('l.previousLevel', 'p');

        $qb
            ->select('COALESCE(p.stock, 0) AS stock', 'COALESCE(p.allocatedStock, 0) AS allocatedStock')
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

        return [
            'stock'          => 0,
            'allocatedStock' => 0,
        ];
    }
}
