<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class ReplenishmentOrderItemInventoryBatchesColumnListener
{
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        $records = $datagrid->getDatasource()->getResults();

        $hasInventoryBatches = false;
        foreach ($records as $record) {
            $value = $record->getValue('inventoryBatches');
            if (!empty($value)) {
                $hasInventoryBatches = true;
                break;
            }
        }
        if ($hasInventoryBatches === false) {
            $config = $datagrid->getConfig();
            $config->offsetSetByPath('[columns][inventoryBatches][renderable]', false);
        }
    }
}
