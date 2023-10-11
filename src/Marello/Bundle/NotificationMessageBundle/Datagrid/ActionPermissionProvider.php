<?php

namespace Marello\Bundle\NotificationMessageBundle\Datagrid;

use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageResolvedInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class ActionPermissionProvider
{
    public function getNotificationMessageActionPermissions(ResultRecordInterface $record): array
    {
        $resolved = $record->getValue('resolved');

        return [
            'resolve' => $resolved === NotificationMessageResolvedInterface::NOTIFICATION_MESSAGE_RESOLVED_NO,
            'view' => true,
        ];
    }
}
