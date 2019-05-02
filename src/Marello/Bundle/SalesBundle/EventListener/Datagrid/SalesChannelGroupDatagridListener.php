<?php

namespace Marello\Bundle\SalesBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;

class SalesChannelGroupDatagridListener
{
    /**
     * @param OrmResultAfter $event
     */
    public function onResultAfter(OrmResultAfter $event)
    {
        $records = $event->getRecords();
        foreach ($records as $k => $record) {
            $value = $record->getValue('salesChannels');
            if ($value->count() < 1) {
                unset($records[$k]);
            }
        }
        $event->setRecords(array_values($records));
    }
}
