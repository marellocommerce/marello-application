<?php

namespace Marello\Bundle\WebhookBundle\Model;

class WebhookContext
{
    public function __construct(
        protected array $data,
        protected string $eventName,
    ) {}

    public function getWebhookDataContext(): array
    {
        return $this->data;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }
}
