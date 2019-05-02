<?php

namespace Marello\Bundle\InventoryBundle\Model;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

class InventoryTotalCalculator
{
    /**
     * {@inheritdoc}
     * @param InventoryItem $item
     * @return int
     */
    public function getTotalInventoryQty(InventoryItem $item)
    {
        return $this->calculateTotalInventoryQuantity($item);
    }

    /**
     * {@inheritdoc}
     * @param InventoryItem $item
     * @return int
     */
    public function getTotalAllocatedInventoryQty(InventoryItem $item)
    {
        return $this->calculateTotalAllocatedInventoryQuantity($item);
    }

    /**
     * {@inheritdoc}
     * @param InventoryItem $item
     * @return int
     */
    public function getTotalVirtualInventoryQty(InventoryItem $item)
    {
        return $this->calculateTotalVirtualInventoryQuantity($item);
    }

    /**
     * @param InventoryItem $inventoryItem
     * @return int
     */
    protected function calculateTotalInventoryQuantity(InventoryItem $inventoryItem)
    {
        $totalInventoryQty = 0;
        if (!$inventoryItem->hasInventoryLevels()) {
            return $totalInventoryQty;
        }

        $inventoryItem->getInventoryLevels()->map(function (InventoryLevel $level) use (&$totalInventoryQty) {
            $totalInventoryQty += $level->getInventoryQty();
        });

        return $totalInventoryQty;
    }

    /**
     * @param InventoryItem $inventoryItem
     * @return int
     */
    protected function calculateTotalAllocatedInventoryQuantity(InventoryItem $inventoryItem)
    {
        $totalAllocatedInventoryQty = 0;
        if (!$inventoryItem->hasInventoryLevels()) {
            return $totalAllocatedInventoryQty;
        }

        $inventoryItem->getInventoryLevels()->map(function (InventoryLevel $level) use (&$totalAllocatedInventoryQty) {
            $totalAllocatedInventoryQty += $level->getAllocatedInventoryQty();
        });

        return $totalAllocatedInventoryQty;
    }

    /**
     * @param InventoryItem $inventoryItem
     * @return int
     */
    protected function calculateTotalVirtualInventoryQuantity(InventoryItem $inventoryItem)
    {
        $totalVirtualQty = 0;
        if (!$inventoryItem->hasInventoryLevels()) {
            return $totalVirtualQty;
        }

        $totalInventoryQty = $this->calculateTotalInventoryQuantity($inventoryItem);
        $totalAllocatedInventoryQty = $this->calculateTotalAllocatedInventoryQuantity($inventoryItem);
        $totalVirtualQty = ($totalInventoryQty - $totalAllocatedInventoryQty);

        return $totalVirtualQty;
    }
}
