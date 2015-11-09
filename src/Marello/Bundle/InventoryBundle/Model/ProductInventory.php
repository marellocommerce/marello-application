<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductInventory
{
    /** @var Collection|WarehouseInventory[] */
    protected $warehouses;

    /** @var Product */
    protected $product;

    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->warehouses = new ArrayCollection();
        $this->product    = $product;
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

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
