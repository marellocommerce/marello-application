<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ProductInventory
{
    /** @var Collection|WarehouseInventory[] */
    protected $warehouses;

    /**
     * ProductInventory constructor.
     */
    public function __construct()
    {
        $this->warehouses = new ArrayCollection();
    }

    /**
     * @return Collection|WarehouseInventory[]
     */
    public function getWarehouses()
    {
        return $this->warehouses;
    }

    /**
     * Modifies and returns inventory items for all warehouses.
     *
     * @return Collection!InventoryItem[]
     */
    public function getModifiedInventoryItems()
    {
        return $this->warehouses->map(function (WarehouseInventory $inventory) {
            return $inventory->getModifiedInventoryItem();
        });
    }
}
