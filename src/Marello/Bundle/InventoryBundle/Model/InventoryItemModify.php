<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryItemModify
{
    const OPERATOR_INCREASE = 'increase';
    const OPERATOR_DECREASE = 'decrease';

    /** @var InventoryItem */
    protected $inventoryItem;

    /** @var int */
    protected $stock = 0;

    /** @var string */
    protected $stockOperator = self::OPERATOR_INCREASE;

    /** @var int */
    protected $allocatedStock = 0;

    /** @var string */
    protected $allocatedStockOperator = self::OPERATOR_INCREASE;

    /**
     * InventoryItemModify constructor.
     *
     * @param $inventoryItem
     */
    public function __construct($inventoryItem)
    {
        $this->inventoryItem = $inventoryItem;
    }

    /**
     * @return mixed
     */
    public function getInventoryItem()
    {
        return $this->inventoryItem;
    }

    /**
     * @return InventoryItem
     */
    public function toModifiedInventoryItem()
    {
        return $this
            ->inventoryItem
            ->adjustStockLevels(
                'manual',
                $this->stock * ($this->stockOperator === self::OPERATOR_INCREASE ? 1 : -1),
                $this->allocatedStock * ($this->allocatedStockOperator === self::OPERATOR_INCREASE ? 1 : -1)
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
     * @return string
     */
    public function getStockOperator()
    {
        return $this->stockOperator;
    }

    /**
     * @param string $stockOperator
     *
     * @return $this
     */
    public function setStockOperator($stockOperator)
    {
        $this->stockOperator = $stockOperator;

        return $this;
    }

    /**
     * @return int
     */
    public function getAllocatedStock()
    {
        return $this->allocatedStock;
    }

    /**
     * @param int $allocatedStock
     *
     * @return $this
     */
    public function setAllocatedStock($allocatedStock)
    {
        $this->allocatedStock = $allocatedStock;

        return $this;
    }

    /**
     * @return string
     */
    public function getAllocatedStockOperator()
    {
        return $this->allocatedStockOperator;
    }

    /**
     * @param string $allocatedStockOperator
     *
     * @return $this
     */
    public function setAllocatedStockOperator($allocatedStockOperator)
    {
        $this->allocatedStockOperator = $allocatedStockOperator;

        return $this;
    }
}
