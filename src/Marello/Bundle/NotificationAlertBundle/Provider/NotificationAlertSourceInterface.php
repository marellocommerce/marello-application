<?php

namespace Marello\Bundle\NotificationAlertBundle\Provider;

interface NotificationAlertSourceInterface
{
    public const NOTIFICATION_ALERT_SOURCE_ENUM_CODE = 'marello_notificationalert_source';
    public const NOTIFICATION_ALERT_SOURCE_ORDER = 'order';
    public const NOTIFICATION_ALERT_SOURCE_PURCHASE_ORDER = 'purchase_order';
    public const NOTIFICATION_ALERT_SOURCE_ALLOCATION = 'allocation';
    public const NOTIFICATION_ALERT_SOURCE_WEBHOOK = 'webhook';
}
