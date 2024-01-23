<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class InventoryLevelRepository extends ServiceEntityRepository
{
    /**
     * @param Warehouse $warehouse
     */
    public function deleteForWarehouse(Warehouse $warehouse)
    {
        $qb = $this->createQueryBuilder('il');
        $qb
            ->delete()
            ->where('il.warehouse = :warehouse')
            ->setParameter('warehouse', $warehouse);
        $query = $qb->getQuery();
        $query->execute();
    }

    /**
     * @param InventoryItem $inventoryItem
     * @param AclHelper $aclHelper
     * @return InventoryLevel[]
     */
    public function findExternalLevelsForInventoryItem(InventoryItem $inventoryItem, AclHelper $aclHelper)
    {
        $qb = $this->createQueryBuilder('il');
        $qb
            ->innerJoin('il.warehouse', 'wh')
            ->innerJoin('wh.warehouseType', 'whType')
            ->andWhere('il.inventoryItem = :inventoryItem')
            ->andWhere('whType.name = :warehouseType')
            ->setParameter('inventoryItem', $inventoryItem)
            ->setParameter('warehouseType', WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);

        return $aclHelper->apply($qb)->getResult();
    }

    public function findWithExpiredSellByDateBatch(): array
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        // Take more than one day gap not to skip anything
        $yesterday = new \DateTime('-25 hours', new \DateTimeZone('UTC'));
        $qb = $this->createQueryBuilder('il');
        $qb
            ->innerJoin('il.inventoryBatches', 'ib')
            ->andWhere($qb->expr()->isNotNull('ib.sellByDate'))
            ->andWhere($qb->expr()->lte('ib.sellByDate', ':now'))
            ->andWhere($qb->expr()->gte('ib.sellByDate', ':yesterday'))
            ->setParameter('now', $now)
            ->setParameter('yesterday', $yesterday);

        return $qb->getQuery()->getResult();
    }
}
