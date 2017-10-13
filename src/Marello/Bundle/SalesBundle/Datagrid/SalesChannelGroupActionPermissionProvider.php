<?php

namespace Marello\Bundle\SalesBundle\Datagrid;

use Marello\Bundle\DataGridBundle\Action\ActionPermissionInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class SalesChannelGroupActionPermissionProvider implements ActionPermissionInterface
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
