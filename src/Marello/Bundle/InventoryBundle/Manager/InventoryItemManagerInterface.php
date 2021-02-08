<?php

namespace Marello\Bundle\InventoryBundle\Manager;

// @todo next release add getDefaultUnitOfMeasurement() as interface method

interface InventoryItemManagerInterface
{
    public function createInventoryItem($product);

    public function getInventoryItemToDelete($product);

    public function hasInventoryItem($product);

    public function getDefaultReplenishment();
}
