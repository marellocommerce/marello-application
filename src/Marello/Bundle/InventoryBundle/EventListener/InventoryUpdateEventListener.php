<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerManager;

class InventoryUpdateEventListener
{
    /**
     * @var InventoryUpdateContext
     */
    protected $manager;

    /**
     * @param InventoryBalancerManager $manager
     */
    public function __construct(InventoryBalancerManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle incoming event
     * @param InventoryUpdateEvent $event
     */
    public function handleUpdateInventoryEvent(InventoryUpdateEvent $event)
    {
        $this->manager->balanceInventory($event->getInventoryUpdateContext());
    }
}
