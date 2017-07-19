<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryLevelLogRecordRepository extends EntityRepository
{
    /**
     * @param InventoryItem $inventoryItem
     * @param \DateTime $from
     * @param \DateTime $to
     * @return array
     */
    public function getInventoryLogRecordsForItem(InventoryItem $inventoryItem, \DateTime $from, \DateTime $to)
    {
        $qb = $this->createQueryBuilder('ir');
        $qb
            ->join('ir.inventoryLevel', 'il');

        /*
         * Select sums of changes and group them by date.
         */
        $qb
            ->select(
                'SUM(ir.inventoryAlteration) AS inventory',
                'SUM(ir.allocatedInventoryAlteration) AS allocatedInventory',
                'DATE(ir.createdAt) AS date'
            )
            ->andWhere($qb->expr()->eq('IDENTITY(il.inventoryItem)', ':inventoryItem'))
            ->andWhere($qb->expr()->between('ir.createdAt', ':from', ':to'))
            ->groupBy('date');

        $qb->setParameters(compact('inventoryItem', 'from', 'to'));

        $results = $qb
            ->getQuery()
            ->getArrayResult();

        return $results;
    }


    /**
     * Returns initial inventory for given day.
     *
     * @param InventoryItem $inventoryItem
     * @param \DateTime $at
     *
     * @return array
     */
    public function getInitialInventory(InventoryItem $inventoryItem, \DateTime $at)
    {
        /*
         * First. Find first record on same day.
         */

        $qb = $this->createQueryBuilder('ir');

        $qb
            ->select(
                'COALESCE(ir.inventoryAlteration, 0) AS inventory',
                'COALESCE(ir.allocatedInventoryAlteration, 0) AS allocatedInventory'
            )
            ->join('ir.inventoryLevel', 'il')
            ->andWhere($qb->expr()->eq('IDENTITY(il.inventoryItem)', ':inventoryItem'))
            ->andWhere('DATE(ir.createdAt) <= DATE(:at)')
            ->orderBy('ir.createdAt', 'DESC');

        $qb
            ->setParameters(compact('inventoryItem', 'at'));

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
