<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryLevelLogRecordRepository extends EntityRepository
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
            ->groupBy('date')
            ->orderBy('date', 'DESC');

        $qb->setParameters(compact('inventoryItem', 'from', 'to'));

        $results = $this->aclHelper
            ->apply($qb)
            ->getArrayResult();

        return $results;
    }
}
