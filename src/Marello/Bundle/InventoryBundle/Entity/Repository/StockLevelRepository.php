<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\StockModify;
use Marello\Bundle\ProductBundle\Entity\Product;

class StockLevelRepository extends EntityRepository
{
    /**
     * @param Product                                           $product
     * @param Warehouse                                         $warehouse
     * @param \Marello\Bundle\InventoryBundle\Model\StockModify $modify
     */
    public function modify(Product $product, Warehouse $warehouse, StockModify $modify)
    {
        $item = $this->getEntityManager()->getRepository(InventoryItem::class)
            ->findOneBy(compact('product', 'warehouse'));

        if (!$item) {
            $item = new InventoryItem($product, $warehouse);
        }

        $this->modifyItem($item, $modify);

        $this->getEntityManager()->persist($item);
    }

    /**
     * @param InventoryItem                                     $inventoryItem
     * @param \Marello\Bundle\InventoryBundle\Model\StockModify $modify
     */
    public function modifyItem(InventoryItem $inventoryItem, StockModify $modify)
    {
        $newLevel = $modify->toStockLevel($inventoryItem);

        $this->getEntityManager()->persist($newLevel);
    }

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
        $qb
            ->join('l.inventoryItem', 'i')
            ->leftJoin('l.previousLevel', 'p');

        /*
         * Select sums of changes and group them by date.
         */
        $qb
            ->select(
                'SUM(l.stock - COALESCE(p.stock, 0)) AS quantity',
                'SUM(l.allocatedStock - COALESCE(p.allocatedStock, 0)) AS allocatedQuantity',
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
            ->leftJoin('l.previousLevel', 'p');

        $qb
            ->select('COALESCE(p.stock, 0) AS quantity', 'COALESCE(p.allocatedStock, 0) AS allocatedQuantity')
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
            'quantity'          => 0,
            'allocatedQuantity' => 0,
        ];
    }
}
