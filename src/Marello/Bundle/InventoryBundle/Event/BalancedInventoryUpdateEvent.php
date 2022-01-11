<?php

namespace Marello\Bundle\InventoryBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class BalancedInventoryUpdateEvent extends Event
{
    const BALANCED_UPDATE_BEFORE = 'marello_inventory.balancedinventory.update_before';
    const BALANCED_UPDATE_AFTER = 'marello_inventory.balancedinventory.update_after';

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
