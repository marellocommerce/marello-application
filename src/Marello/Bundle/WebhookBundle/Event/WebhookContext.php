<?php

namespace Marello\Bundle\WebhookBundle\Event;

use Marello\Bundle\WebhookBundle\Entity\Webhook;

class WebhookContext
{
    /** @var array $values */
    protected array $data;

    /** @var $webhooks Webhook[] */
    protected array $webhooks;

    protected string $eventName;

    /**
     * @param array|string $data
     * @param string $eventName
     * @param array $webhooks
     */
    public function __construct(
        array|string $data,
        string       $eventName,
        array        $webhooks = [] //if empty notify all active webhooks related to this event
    ) {
        $this->data = $data;
        $this->webhooks = $webhooks;
        $this->eventName = $eventName;
    }

    public function getWebhookDataContext(): array
    {
        return $this->data;
    }

    public function getWebhooks(): array
    {
        return $this->webhooks;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }
}
