<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\ProductBundle\Entity\Product;

class InventoryLevelRepository extends EntityRepository
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
            ->join('l.inventoryItem', 'i');

        /*
         * Select sums of changes and group them by date.
         */
        $qb
            ->select(
                'SUM(l.inventoryAlteration) AS inventory',
                'SUM(l.allocatedInventoryAlteration) AS allocatedInventory',
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
            ->select(
                'COALESCE(l.inventoryAlteration, 0) AS inventory',
                'COALESCE(l.allocatedInventoryAlteration, 0) AS allocatedInventory'
            )
            ->join('l.inventoryItem', 'i')
            ->andWhere($qb->expr()->eq('IDENTITY(i.product)', ':product'))
            ->andWhere('DATE(l.createdAt) <= DATE(:at)')
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

        return [
            'inventory'          => 0,
            'allocatedInventory' => 0,
        ];
    }
}
