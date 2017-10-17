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
        $system = $record->getValue('system');
        return [
            'update' => !$system,
            'view' => true,
            'delete' => !$system,
        ];
    }
}
