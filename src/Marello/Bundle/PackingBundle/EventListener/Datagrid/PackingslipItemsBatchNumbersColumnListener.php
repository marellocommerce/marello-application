<?php

namespace Marello\Bundle\PackingBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class PackingslipItemsBatchNumbersColumnListener
{
    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        $records = $datagrid->getDatasource()->getResults();

        $hasInventoryBatches = false;
        foreach ($records as $k => $record) {
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
