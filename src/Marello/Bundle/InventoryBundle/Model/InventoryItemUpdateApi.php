<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class InventoryItemUpdateApi
{
    /** @var InventoryItem|null */
    protected $inventoryItem = null;

    /** @var Warehouse */
    protected $warehouse;

    /** @var int */
    protected $stock;

    /**
     * InventoryItemUpdateApi constructor.
     *
     * @param InventoryItem|null $inventoryItem
     */
    public function __construct(InventoryItem $inventoryItem = null)
    {
        $this->inventoryItem = $inventoryItem;
        $this->stock         = $inventoryItem ? $inventoryItem->getStock() : 0;
        $this->warehouse     = $inventoryItem ? $inventoryItem->getWarehouse() : null;
    }

    /**
     * @return InventoryItem
     */
    public function toInventoryItem()
    {
        if (!$this->warehouse) {
            return null;
        }

        if (!$this->inventoryItem) {
            $this->inventoryItem = new InventoryItem($this->warehouse);
        }

        return $this
            ->inventoryItem
            ->adjustStockLevels(
                'import',
                $this->stock
            );
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     *
     * @return $this
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param Warehouse $warehouse
     *
     * @return $this
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }
}
