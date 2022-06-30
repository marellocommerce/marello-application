<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryLevelLogRecordRepository extends ServiceEntityRepository
{
    /**
     * @param InventoryItem $inventoryItem
     * @param \DateTime $from
     * @param \DateTime $to
     * @param AclHelper $aclHelper
     * @return array
     */
    public function getInventoryLogRecordsForItem(
        InventoryItem $inventoryItem,
        \DateTime $from,
        \DateTime $to,
        AclHelper $aclHelper
    ) {
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
            ->groupBy('date')
            ->orderBy('date', 'DESC');

        $qb->setParameters(compact('inventoryItem', 'from', 'to'));

        $results = $aclHelper
            ->apply($qb)
            ->getArrayResult();

        return $results;
    }
}
