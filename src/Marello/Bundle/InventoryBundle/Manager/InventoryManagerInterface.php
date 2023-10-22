<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

interface InventoryManagerInterface
{
    /**
     * Update inventory items based of context and calculate new inventory level
     * @param InventoryUpdateContext $context
     */
    public function updateInventoryLevel(InventoryUpdateContext $context): void;
}
