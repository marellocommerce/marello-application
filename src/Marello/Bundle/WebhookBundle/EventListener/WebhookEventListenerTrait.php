<?php

namespace Marello\Bundle\WebhookBundle\EventListener;

use Marello\Bundle\WebhookBundle\Event\WebhookContext;
use Marello\Bundle\WebhookBundle\Event\WebhookEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

trait WebhookEventListenerTrait
{
    protected $eventDispatcher;

    /**
     * Provide data to the webhook. Here webhook_id && data keys are compulsory
     * @param Event $event
     * @return WebhookContext
     */
    public function triggerWebhookNotificationEvent(Event $event): WebhookContext
    {
        $context = $this->getWebhookDataContext($event);
        $this->eventDispatcher->dispatch(
            new WebhookEvent($context),
            WebhookEvent::NAME
        );
        return $context;
    }

    public function setEventDispatcher($eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}