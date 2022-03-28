<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class InventoryLevelRepository extends EntityRepository
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
     * @return InventoryLevel[]
     */
    public function findExternalLevelsForInventoryItem(InventoryItem $inventoryItem)
    {
        $qb = $this->createQueryBuilder('il');
        $qb
            ->innerJoin('il.warehouse', 'wh')
            ->innerJoin('wh.warehouseType', 'whType')
            ->andWhere('il.inventoryItem = :inventoryItem')
            ->andWhere('whType.name = :warehouseType')
            ->setParameter('inventoryItem', $inventoryItem)
            ->setParameter('warehouseType', WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);

        return $this->aclHelper->apply($qb)->getResult();
    }
}
