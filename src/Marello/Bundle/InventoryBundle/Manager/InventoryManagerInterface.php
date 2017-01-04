<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

interface InventoryManagerInterface
{
    public function updateInventoryItems(InventoryUpdateContext $context);
}
