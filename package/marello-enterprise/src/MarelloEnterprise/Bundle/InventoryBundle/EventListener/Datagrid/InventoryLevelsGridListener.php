<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Event\OrmResultBeforeQuery;

class InventoryLevelsGridListener
{
    /**
     * @param OrmResultBeforeQuery $event
     */
    public function onResultBeforeQuery(OrmResultBeforeQuery $event)
    {
        $event
            ->getQueryBuilder()
            ->leftJoin('il.warehouse', 'warehouse');
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $columns = $config->offsetGetOr('columns', []);
        $finalColumns = array_merge(
            [
                'warehouse' => [
                    'label' => 'marello.inventory.inventorylevel.warehouse.label',
                    'frontend_type' => 'string'
                ]
            ],
            $columns
        );
        $config->offsetSet('columns', $finalColumns);
    }
}
