<?php

namespace Marello\Bundle\WebhookBundle\Event\Provider;

use Marello\Bundle\WebhookBundle\Event\WebhookEventInterface;

class WebhookEventProvider
{
    /**
     * @var WebhookEventInterface[]
     */
    private array $events;

    public function __construct(iterable $events)
    {
        $this->events = $events instanceof \Traversable ? iterator_to_array($events) : $events;
    }

    /**
     * @return WebhookEventInterface[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function getEvent(string $name): ?WebhookEventInterface
    {
        if ($this->hasEvent($name)) {
            return $this->events[$name];
        }

        return null;
    }

    public function hasEvent(string $name): bool
    {
        return isset($this->events[$name]);
    }

    public function getEventChoices(): array
    {
        $choices = [];
        foreach ($this->events as $event) {
            $choices[$event::getLabel()] = $event::getName();
        }

        return $choices;
    }

    public function getLabel(string $name): string
    {
        $event = $this->getEvent($name);
        if ($event) {
            return $event::getLabel();
        }

        return '';
    }
}
