<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class InventoryUpdateEventListener
{
    /**
     * @var InventoryUpdateContext
     */
    protected $manager;

    /**
     * @param InventoryManager $manager
     */
    public function __construct(InventoryManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle incoming event
     * @param InventoryUpdateEvent $event
     */
    public function handleUpdateInventoryEvent(InventoryUpdateEvent $event)
    {
        $this->manager->updateInventoryItems($event->getInventoryUpdateContext());
    }
}
