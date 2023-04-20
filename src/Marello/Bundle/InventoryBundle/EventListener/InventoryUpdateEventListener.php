<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\WebhookBundle\Event\WebhookContext;
use Marello\Bundle\WebhookBundle\EventListener\WebhookListenerInterface;
use Marello\Bundle\WebhookBundle\EventListener\WebhookEventListenerTrait;
use Symfony\Contracts\EventDispatcher\Event;

class InventoryUpdateEventListener implements  WebhookListenerInterface
{
    use WebhookEventListenerTrait;

    /** @var InventoryManager $manager */
    protected $manager;

    /** @var BalancedInventoryManager $balancedInventoryManager */
    protected $balancedInventoryManager;

    /**
     * @param InventoryManager $manager
     * @param BalancedInventoryManager $balancedInventoryManager
     */
    public function __construct(
        InventoryManager $manager,
        BalancedInventoryManager $balancedInventoryManager
    ) {
        $this->manager = $manager;
        $this->balancedInventoryManager = $balancedInventoryManager;
    }

    /**
     * Handle incoming event
     * @param InventoryUpdateEvent $event
     */
    public function handleUpdateInventoryEvent(InventoryUpdateEvent $event)
    {
        $context = $event->getInventoryUpdateContext();
        if (!$context->getIsVirtual()) {
            $this->manager->updateInventoryLevel($context);
            return;
        }

        $this->balancedInventoryManager->updateInventoryLevel($context);
    }

    /**
     * @return string
     */
    public function getRegisteredWebhook(): string
    {
        return 'marello_inventory.inventory.update';
    }

    /**
     * @param InventoryUpdateEvent|Event $event
     * @return WebhookContext
     */
    public function getWebhookDataContext(InventoryUpdateEvent|Event $event): WebhookContext
    {
        /** @var InventoryUpdateContext $inventoryContext */
        $inventoryContext = $event->getInventoryUpdateContext();

        $data[] = [
            'inventory' => $inventoryContext->getInventory(),
            'allocated_inventory_qty' => $inventoryContext->getAllocatedInventory(),
            'sku' => $inventoryContext->getInventoryItem()->getProduct()->getSku(),
        ];

        return new WebhookContext($data, $this->getRegisteredWebhook());
    }
}
