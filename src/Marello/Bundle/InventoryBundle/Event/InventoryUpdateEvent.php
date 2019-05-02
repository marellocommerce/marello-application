<?php

namespace Marello\Bundle\InventoryBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class InventoryUpdateEvent extends Event
{
    const NAME = 'marello_inventory.inventory.update';

    const INVENTORY_UPDATE_BEFORE = 'marello_inventory.inventory.update_before';
    const INVENTORY_UPDATE_AFTER = 'marello_inventory.inventory.update_after';
    /**
     * @var InventoryUpdateContext
     */
    protected $context;

    /**
     * @param InventoryUpdateContext $context
     */
    public function __construct(InventoryUpdateContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return InventoryUpdateContext
     */
    public function getInventoryUpdateContext()
    {
        return $this->context;
    }
}
