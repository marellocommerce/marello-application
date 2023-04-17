<?php

namespace Marello\Bundle\NotificationAlertBundle\Provider;

interface NotificationAlertTypeInterface
{
    public const NOTIFICATION_ALERT_TYPE_ENUM_CODE = 'marello_notificationalert_alerttype';
    public const NOTIFICATION_ALERT_TYPE_ERROR = 'error';
    public const NOTIFICATION_ALERT_TYPE_WARNING = 'warning';
    public const NOTIFICATION_ALERT_TYPE_SUCCESS = 'success';
    public const NOTIFICATION_ALERT_TYPE_INFO = 'info';
}
