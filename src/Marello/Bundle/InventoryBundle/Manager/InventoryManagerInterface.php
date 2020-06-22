<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

interface InventoryManagerInterface
{
    /**
     * {@inheritdoc}
     * @param InventoryUpdateContext $context
     */
    public function updateInventoryLevel(InventoryUpdateContext $context);
}
