<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
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
        $adjustment = $this->calculateAdjustment($operator, $inventoryQuantity);
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
        $adjustment = $this->calculateAdjustment($operator, $inventoryAllocatedQuantity);
        return $inventoryLevel->getAllocatedInventoryQty() + $adjustment;
    }

    /**
     * @param array $batches
     * @return int
     * @throws \Exception
     */
    public function calculateBatchInventoryLevelQty(array $batches)
    {
        // check level's inventory batches for expiration dates
        $currentDateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $batchInventoryTotal = 0;
        foreach ($batches as $inventoryBatch) {
            $batch = is_array($inventoryBatch) ? $inventoryBatch['batch'] : $inventoryBatch;
            if (!$batch instanceof InventoryBatch) {
                continue;
            }

            // we cannot use expired batches
            if (!$batch->getSellByDate() || $batch->getSellByDate() > $currentDateTime) {
                $batchInventoryTotal += $batch->getQuantity();
            }
        }

        return $batchInventoryTotal;
    }

    /**
     * @param string $operator
     * @param int $quantity
     * @return int
     */
    public function calculateAdjustment($operator, $quantity)
    {
        $adjustmentOperator = $this->getAdjustmentOperator($operator);
        $adjustment = $this->getAdjustment($adjustmentOperator, $quantity);

        return (int) $adjustment;
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
        // prevent giving back a positive adjustment while it should be a negative one
        if ($operator === self::OPERATOR_DECREASE && $quantity < 0) {
            $quantity = ($quantity * -1);
        }

        return ($quantity * $operator);
    }
}
