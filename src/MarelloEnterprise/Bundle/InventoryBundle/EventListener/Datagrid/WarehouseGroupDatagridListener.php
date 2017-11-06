<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;

class WarehouseGroupDatagridListener
{
    /**
     * @param OrmResultAfter $event
     */
    public function onResultAfter(OrmResultAfter $event)
    {
        $records = $event->getRecords();
        foreach ($records as $k => $record) {
            $value = $record->getValue('warehouses');
            if ($value->count() === 0) {
                unset($records[$k]);
            }
        }
        $event->setRecords(array_values($records));
    }
}
