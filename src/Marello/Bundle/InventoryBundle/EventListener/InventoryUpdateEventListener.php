<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Manager\VirtualInventoryManager;

class InventoryUpdateEventListener
{
    /** @var InventoryManager $manager */
    protected $manager;

    /** @var VirtualInventoryManager $virtualInventoryManager */
    protected $virtualInventoryManager;

    /**
     * @param InventoryManager $manager
     * @param VirtualInventoryManager $virtualInventoryManager
     */
    public function __construct(
        InventoryManager $manager,
        VirtualInventoryManager $virtualInventoryManager
    ) {
        $this->manager = $manager;
        $this->virtualInventoryManager = $virtualInventoryManager;
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

        $this->virtualInventoryManager->updateInventoryLevel($context);
    }
}
