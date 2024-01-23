<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateWebhookEvent;
use Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\WebhookBundle\Manager\WebhookProducer;

class InventoryUpdateEventListener
{
    public function __construct(
        protected InventoryManager $manager,
        protected BalancedInventoryManager $balancedInventoryManager,
        protected WebhookProducer $webhookProducer
    ) {}

    /**
     * Handle incoming event
     * @param InventoryUpdateEvent $event
     */
    public function handleUpdateInventoryEvent(InventoryUpdateEvent $event)
    {
        $context = $event->getInventoryUpdateContext();
        $this->webhookProducer->triggerWebhook(new InventoryUpdateWebhookEvent($context));
        if ($context->getIsVirtual()) {
            $this->balancedInventoryManager->updateInventoryLevel($context);
            return;
        }

        $this->manager->updateInventoryLevel($context);
    }
}
