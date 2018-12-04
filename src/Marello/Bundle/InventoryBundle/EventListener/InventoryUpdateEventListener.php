<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Manager\BalancedInventoryManager;

class InventoryUpdateEventListener
{
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
}
