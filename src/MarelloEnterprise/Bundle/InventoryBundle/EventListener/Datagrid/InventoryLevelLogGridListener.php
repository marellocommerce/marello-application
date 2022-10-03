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

        if (array_key_exists('batchNumber', $columns)) {
            $offset = array_search('batchNumber', array_keys($columns));
            $warehouseColumn = [
                'warehouseLabel' => [
                    'label' => 'marello.inventory.inventorylevel.warehouse.label',
                    'frontend_type' => 'string'
                ]
            ];
            $spliced = array_splice($columns, 0, $offset+1);
            $finalColumns = array_merge($spliced, $warehouseColumn, $columns);
            $config->offsetSet('columns', $finalColumns);
        }
    }
}
