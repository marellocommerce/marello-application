<?php

namespace Marello\Bundle\WebhookBundle\EventListener;

use Marello\Bundle\WebhookBundle\Event\WebhookContext;
use Symfony\Contracts\EventDispatcher\Event;

interface WebhookListenerInterface
{
    public function getRegisteredWebhook(): string;

    public function getWebhookDataContext(Event $event): WebhookContext;
}
