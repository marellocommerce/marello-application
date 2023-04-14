<?php

namespace Marello\Bundle\WebhookBundle\Manager;

use Marello\Bundle\WebhookBundle\EventListener\WebhookListenerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manager to control WebhookListenerInterface execution
 */
class WebhookListenerManager
{
    /**
     * @var WebhookListenerInterface[]
     */
    protected $webhookListeners = [];

    /**
     * @var WebhookListenerInterface[]
     */
    protected $disabledListeners = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(array $webhookListeners, ContainerInterface $container)
    {
        $this->webhookListeners = $webhookListeners;
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getListeners()
    {
        return $this->webhookListeners;
    }

    /**
     * Set one listener as disabled
     *
     * @param string $listenerId
     *
     * @throws \InvalidArgumentException When given listener doesn't exist
     */
    public function disableListener($listenerId)
    {
        if (in_array($listenerId, $this->webhookListeners)) {
            $this->container->get($listenerId)->setEnabled(false);

            $this->disabledListeners[$listenerId] = $listenerId;
        } else {
            throw new \InvalidArgumentException(
                sprintf('Listener "%s" does not exist or not optional', $listenerId)
            );
        }
    }

    /**
     * Disable specified listeners
     */
    public function disableListeners(array $listeners)
    {
        foreach ($listeners as $listener) {
            $this->disableListener($listener);
        }
    }

    /**
     * Set one listener as enabled
     *
     * @param string $listenerId
     *
     * @throws \InvalidArgumentException When given listener doesn't exist
     */
    public function enableListener($listenerId)
    {
        if (in_array($listenerId, $this->webhookListeners)) {
            $this->container->get($listenerId)->setEnabled(true);

            unset($this->disabledListeners[$listenerId]);
        } else {
            throw new \InvalidArgumentException(
                sprintf('Listener "%s" does not exist or not optional', $listenerId)
            );
        }
    }

    /**
     * Enable specified listeners
     */
    public function enableListeners(array $listeners)
    {
        foreach ($listeners as $listener) {
            $this->enableListener($listener);
        }
    }

    public function getDisabledListeners(): array
    {
        return array_values($this->disabledListeners);
    }
}
