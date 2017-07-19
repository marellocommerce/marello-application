<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

class InventoryLevelCalculator
{
    const OPERATOR_INCREASE = 'increase';
    const OPERATOR_DECREASE = 'decrease';

    /**
     * @param InventoryLevel $inventoryLevel
     * @param $inventoryQuantity
     * @param $operator
     * @return mixed
     */
    public function calculateInventoryQuantity(
        $inventoryLevel,
        $inventoryQuantity,
        $operator
    ) {
        $adjustmentOperator = $this->getAdjustmentOperator($operator);
        $adjustment = $this->getAdjustment($adjustmentOperator, $inventoryQuantity);
        return $inventoryLevel->getInventoryQty() + $adjustment;
    }

    /**
     * @param InventoryLevel $inventoryLevel
     * @param $inventoryAllocatedQuantity
     * @param $operator
     * @return mixed
     */
    public function calculateAllocatedInventoryQuantity(
        $inventoryLevel,
        $inventoryAllocatedQuantity,
        $operator
    ) {
        $adjustmentOperator = $this->getAdjustmentOperator($operator);
        $adjustment = $this->getAdjustment($adjustmentOperator, $inventoryAllocatedQuantity);
        return $inventoryLevel->getAllocatedInventoryQty() + $adjustment;
    }

    /**
     * @param $operator
     * @return int
     */
    protected function getAdjustmentOperator($operator)
    {
        return ($operator === self::OPERATOR_INCREASE ? 1 : -1);
    }

    /**
     * @param $operator
     * @param $quantity
     * @return mixed
     */
    protected function getAdjustment($operator, $quantity)
    {
        return ($quantity * $operator);
    }
}