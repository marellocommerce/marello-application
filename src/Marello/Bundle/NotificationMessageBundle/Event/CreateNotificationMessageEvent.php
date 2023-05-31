<?php

namespace Marello\Bundle\NotificationMessageBundle\Event;

use Marello\Bundle\NotificationMessageBundle\Model\NotificationMessageContext;
use Symfony\Contracts\EventDispatcher\Event;

class CreateNotificationMessageEvent extends Event
{
    public const NAME = 'marello_notificationmessage.create_notification_message';

    public function __construct(
        private NotificationMessageContext $context
    ) {}

    public function getContext(): NotificationMessageContext
    {
        return $this->context;
    }
}
