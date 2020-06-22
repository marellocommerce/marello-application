<?php

namespace Marello\Bundle\InventoryBundle\Manager;

interface InventoryItemManagerInterface
{
    public function createInventoryItem($product);

    public function getInventoryItemToDelete($product);

    public function hasInventoryItem($product);

    public function getDefaultReplenishment();
}
