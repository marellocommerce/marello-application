<?php

namespace Marello\Bundle\NotificationAlertBundle\Event;

use Marello\Bundle\NotificationAlertBundle\Model\NotificationAlertContext;
use Symfony\Contracts\EventDispatcher\Event;

class CreateNotificationAlertEvent extends Event
{
    public const NAME = 'marello_notificationalert.create_notification_alert';

    public function __construct(
        private NotificationAlertContext $context
    ) {}

    public function getContext(): NotificationAlertContext
    {
        return $this->context;
    }
}
