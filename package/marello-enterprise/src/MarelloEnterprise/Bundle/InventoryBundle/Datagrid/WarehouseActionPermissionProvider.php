<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Datagrid;

use Marello\Bundle\DataGridBundle\Action\ActionPermissionInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class WarehouseActionPermissionProvider implements ActionPermissionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getActionPermissions(ResultRecordInterface $record)
    {
        return [
            'update' => true,
            'view' => true,
            'delete' => !$record->getValue('default'),
        ];
    }
}
