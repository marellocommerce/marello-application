<?php

namespace Marello\Bundle\WebhookBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class WebhookEvent extends Event
{
    public const NAME = 'marello_webhook.notify_webhook';

    /**
     * @var WebhookContext
     */
    protected WebhookContext $context;

    /**
     * @param WebhookContext $context
     */
    public function __construct(WebhookContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return WebhookContext
     */
    public function getWebhookContext(): WebhookContext
    {
        return $this->context;
    }
}