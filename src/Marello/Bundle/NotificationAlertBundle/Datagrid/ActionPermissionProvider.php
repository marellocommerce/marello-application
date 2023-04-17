<?php

namespace Marello\Bundle\NotificationAlertBundle\Datagrid;

use Marello\Bundle\NotificationAlertBundle\Provider\NotificationAlertResolvedInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class ActionPermissionProvider
{
    public function getNotificationAlertActionPermissions(ResultRecordInterface $record): array
    {
        $resolved = $record->getValue('resolved');

        return [
            'resolve' => $resolved === NotificationAlertResolvedInterface::NOTIFICATION_ALERT_RESOLVED_NO,
            'view' => true,
        ];
    }
}
