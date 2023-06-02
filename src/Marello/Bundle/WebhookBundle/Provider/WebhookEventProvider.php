<?php

namespace Marello\Bundle\WebhookBundle\Provider;

use Marello\Bundle\WebhookBundle\Event\WebhookEventInterface;

class WebhookEventProvider
{
    /**
     * @var WebhookEventInterface[]
     */
    private array $events = [];

    /**
     * @param WebhookEventInterface $webhookEvent
     * @return $this
     */
    public function addEvent(WebhookEventInterface $webhookEvent)
    {
        $name = $webhookEvent->getName();
        if ($this->hasEvent($name)) {
            throw new \LogicException(sprintf('Event "%s" already registered', $name));
        }
        $this->events[$name] = $webhookEvent;

        return $this;
    }

    /**
     * @return WebhookEventInterface[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param string $name
     * @return WebhookEventInterface|null
     */
    public function getEvent(string $name): ?WebhookEventInterface
    {
        if ($this->hasEvent($name)) {
            return $this->events[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasEvent(string $name): bool
    {
        return isset($this->events[$name]);
    }
}
