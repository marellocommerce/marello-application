<?php

namespace Marello\Bundle\InventoryBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class VirtualInventoryUpdateEvent extends Event
{
    const VIRTUAL_UPDATE_BEFORE = 'marello_inventory.virtualinventory.update_before';
    const VIRTUAL_UPDATE_AFTER = 'marello_inventory.virtualinventory.update_after';

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
