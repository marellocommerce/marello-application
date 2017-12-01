<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

interface InventoryManagerInterface
{
    /**
     * @deprecated use updateInventoryLevel instead
     * @param InventoryUpdateContext $context
     */
    public function updateInventoryItems(InventoryUpdateContext $context);

    /**
     * @param InventoryUpdateContext $context
     * @BC_BREAK
     */
    public function updateInventoryLevel(InventoryUpdateContext $context);
}
