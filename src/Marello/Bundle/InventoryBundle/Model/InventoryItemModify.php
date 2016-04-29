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
     * @return StockModify
     */
    public function toStockModify()
    {
        return new StockModify(
            'manual',
            $this->stock * ($this->stockOperator === self::OPERATOR_INCREASE ? 1 : -1),
            $this->allocatedStock * ($this->allocatedStockOperator === self::OPERATOR_INCREASE ? 1 : -1)
        );
    }

    /**
     * @return InventoryItem
     */
    public function toModifiedInventoryItem()
    {
        return $this->toStockModify()
            ->toStockLevel($this->inventoryItem)
            ->getInventoryItem();
    }
}
