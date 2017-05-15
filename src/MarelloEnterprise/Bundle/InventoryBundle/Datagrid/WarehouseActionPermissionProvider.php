<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

use Marello\Bundle\DataGridBundle\Action\ActionPermissionInterface;

class WarehouseActionPermissionProvider implements ActionPermissionInterface
{
    /**
     * @param ResultRecordInterface $record
     * @return array
     */
    public function getActionPermissions(ResultRecordInterface $record)
    {
        return [
            'update' => true,
            'delete' => !$record->getValue('default'),
        ];
    }
}
