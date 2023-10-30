<?php

namespace Marello\Bundle\WebhookBundle\Event;

use Marello\Bundle\WebhookBundle\Model\WebhookContext;

abstract class AbstractWebhookEvent implements WebhookEventInterface
{
    public function __construct(
        protected $data = null
    ) {}

    abstract public static function getName(): string;

    public function getContext(): WebhookContext
    {
        return new WebhookContext(
            $this->getContextData(),
            static::getName()
        );
    }

    abstract protected function getContextData(): array;
}
