<?php

namespace Marello\Bundle\NotificationMessageBundle\Provider;

interface NotificationMessageTypeInterface
{
    public const NOTIFICATION_MESSAGE_TYPE_ENUM_CODE = 'marello_notificationmessage_alerttype';
    public const NOTIFICATION_MESSAGE_TYPE_ERROR = 'error';
    public const NOTIFICATION_MESSAGE_TYPE_WARNING = 'warning';
    public const NOTIFICATION_MESSAGE_TYPE_SUCCESS = 'success';
    public const NOTIFICATION_MESSAGE_TYPE_INFO = 'info';
}
