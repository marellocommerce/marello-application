<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\DataGrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class WarehouseGridDeleteActionChecker
{
    /**
     * @return \Closure
     */
    public function getCheckCallback()
    {
        return function (ResultRecordInterface $record) {
            return [
                'update' => true,
                'delete' => !$record->getValue('default'),
            ];
        };
    }
}
