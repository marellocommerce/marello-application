<?php

namespace Marello\Bundle\NotificationMessageBundle\Provider;

interface NotificationMessageSourceInterface
{
    public const NOTIFICATION_MESSAGE_SOURCE_ENUM_CODE = 'marello_notificationmessage_source';
    public const NOTIFICATION_MESSAGE_SOURCE_ORDER = 'order';
    public const NOTIFICATION_MESSAGE_SOURCE_PURCHASE_ORDER = 'purchase_order';
    public const NOTIFICATION_MESSAGE_SOURCE_ALLOCATION = 'allocation';
    public const NOTIFICATION_MESSAGE_SOURCE_WEBHOOK = 'webhook';
}
