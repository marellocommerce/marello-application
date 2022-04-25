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
}
