<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Datagrid;

use Marello\Bundle\DataGridBundle\Action\ActionPermissionInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class WarehouseChannelGroupLinkActionPermissionProvider implements ActionPermissionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getActionPermissions(ResultRecordInterface $record)
    {
        $system = $record->getValue('system');
        
        return [
            'update' => !$system,
            'view' => true,
            'delete' => !$system,
        ];
    }
}
