<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

interface InventoryBalancerInterface
{
    public function process(InventoryUpdateContext $context);
}
