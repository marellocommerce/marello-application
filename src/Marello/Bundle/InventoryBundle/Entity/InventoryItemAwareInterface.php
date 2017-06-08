<?php

namespace Marello\Bundle\InventoryBundle\Entity;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

interface InventoryItemAwareInterface
{
    public function getInventoryItems();
}