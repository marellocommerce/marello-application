<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Datagrid;

use Marello\Bundle\DataGridBundle\Action\ActionPermissionInterface;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Checker\IsFixedWarehouseGroupChecker;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class WarehouseChannelGroupLinkActionPermissionProvider implements ActionPermissionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getActionPermissions(ResultRecordInterface $record)
    {
        return [
            'update' => !$record->getValue('system'),
            'view' => true,
            'delete' => !$record->getValue('system'),
        ];
    }
}
