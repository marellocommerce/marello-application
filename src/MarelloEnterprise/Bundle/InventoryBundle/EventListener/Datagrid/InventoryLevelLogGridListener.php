<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Event\OrmResultBeforeQuery;

class InventoryLevelLogGridListener
{
    /**
     * @param OrmResultBeforeQuery $event
     */
    public function onResultBeforeQuery(OrmResultBeforeQuery $event)
    {
        $qb =  $event->getQueryBuilder();
        $qb
            ->addSelect('lr.warehouseName as warehouseLabel');
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $columns = $config->offsetGetOr('columns', []);

        $warehouseColumn = [
            'warehouseLabel' => [
                'label' => 'marello.inventory.inventorylevel.warehouse.label',
                'frontend_type' => 'string',
                'order' => 25
            ]
        ];
        $columns = array_merge($warehouseColumn, $columns);
        $config->offsetSet('columns', $columns);
    }
}
