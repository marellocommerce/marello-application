<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Symfony\Component\Validator\Constraints as Assert;

class InventoryItemModify
{
    const OPERATOR_INCREASE = 'increase';
    const OPERATOR_DECREASE = 'decrease';

    /**
     * Operator for modification (increase or decrease).
     *
     * @var string
     */
    protected $modifyOperator = self::OPERATOR_INCREASE;

    /**
     * Amount to increase/decrease the quantity.
     *
     * @Assert\GreaterThanOrEqual(value=0)
     *
     * @var int
     */
    protected $modifyAmount = 0;

    /**
     * Reference to inventory item. This inventory item is modified. If no reference exists before modification,
     * new inventory item is created with appropriate quantity.
     *
     * @var InventoryItem
     */
    protected $inventoryItem = null;

    /**
     * InventoryItemModify constructor.
     *
     * @param InventoryItem $inventoryItem
     */
    public function __construct(InventoryItem $inventoryItem)
    {
        $this->inventoryItem = $inventoryItem;
    }

    /**
     * Modifies underlying inventory item.
     *
     * @return $this
     */
    public function modify()
    {
        if ($this->modifyOperator === self::OPERATOR_INCREASE) {
            $this->inventoryItem->modifyQuantity($this->modifyAmount);
        } else {
            $this->inventoryItem->modifyQuantity(-$this->modifyAmount);
        }

        return $this;
    }

    /**
     * @return InventoryItem
     */
    public function getInventoryItem()
    {
        return $this->inventoryItem;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->inventoryItem->getWarehouse();
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->inventoryItem->getProduct();
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->inventoryItem->getQuantity();
    }

    /**
     * @return int
     */
    public function getModifyAmount()
    {
        return $this->modifyAmount;
    }

    /**
     * @param int $modifyAmount
     *
     * @return $this
     */
    public function setModifyAmount($modifyAmount)
    {
        $this->modifyAmount = $modifyAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getModifyOperator()
    {
        return $this->modifyOperator;
    }

    /**
     * @param string $modifyOperator
     *
     * @return $this
     */
    public function setModifyOperator($modifyOperator)
    {
        $this->modifyOperator = $modifyOperator;

        return $this;
    }
}
