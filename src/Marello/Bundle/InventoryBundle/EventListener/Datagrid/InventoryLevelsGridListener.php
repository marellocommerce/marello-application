<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Datagrid;

use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Oro\Bundle\DataGridBundle\Event\OrmResultBeforeQuery;

class InventoryLevelsGridListener
{
    /**
     * @param OrmResultBeforeQuery $event
     */
    public function onResultBeforeQuery(OrmResultBeforeQuery $event)
    {
        $qb =  $event->getQueryBuilder();
        $qb
            ->leftJoin('il.warehouse', 'wh')
            ->leftJoin('wh.warehouseType', 'whType')
            ->andWhere('whType.name <> :warehouseType')
            ->setParameter('warehouseType', WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);
    }
}
