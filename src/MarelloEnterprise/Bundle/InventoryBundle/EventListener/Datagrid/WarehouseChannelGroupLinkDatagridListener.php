<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Datagrid;

use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;

class WarehouseChannelGroupLinkDatagridListener
{
    /**
     * @param OrmResultAfter $event
     */
    public function onResultAfter(OrmResultAfter $event)
    {
        $records = $event->getRecords();
        foreach ($records as $k => $record) {
            /** @var WarehouseGroup $warehouseGroup */
            $warehouseGroup = $record->getValue('warehouseGroup');
            /** @var SalesChannelGroup[] $salesChannelGroups */
            $salesChannelGroups = $record->getValue('salesChannelGroups');
            if ($warehouseGroup->getWarehouses()->count() === 0) {
                unset($records[$k]);
            } else {
                foreach ($salesChannelGroups as $salesChannelGroup) {
                    if ($salesChannelGroup->getSalesChannels()->count() === 0) {
                        unset($records[$k]);
                        break;
                    }
                }
            }
        }
        $event->setRecords(array_values($records));
    }
}
