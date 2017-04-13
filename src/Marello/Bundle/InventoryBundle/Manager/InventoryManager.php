<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Entity\StockLevel;
use Oro\Bundle\UserBundle\Entity\User;

class InventoryManager implements InventoryManagerInterface
{
    /**
     * Update inventory items based of context and calculate new inventory level
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryItems(InventoryUpdateContext $context)
    {
        if (!$this->validateItems($context)) {
            throw new \Exception('Item structure not valid.');
        }

        $items = $context->getItems();
        /** @var InventoryItem $item */
        foreach ($items as $data) {
            $stock = null;
            $allocatedStock = null;
            if ($context->getStock()) {
                $stock = ($data['item']->getStock() + $context->getStock());
            }

            if ($context->getAllocatedStock()) {
                $allocatedStock = ($data['item']->getAllocatedStock() + $context->getAllocatedStock());
            }

            $this->updateInventoryLevel(
                $data['item'],
                $context->getChangeTrigger(),
                $stock,
                $context->getStock(),
                $allocatedStock,
                $context->getAllocatedStock(),
                $context->getUser(),
                $context->getRelatedEntity()
            );
        }
    }

    /**
     * @param InventoryItem     $item                   InventoryItem to be updated
     * @param string            $trigger                Action that triggered the change
     * @param int|null          $inventory              New inventory or null if it should remain unchanged
     * @param int|null          $inventoryAlt           Inventory Change qty, qty that represents the actual change
     * @param int|null          $allocatedInventory     New allocated inventory or null if it should remain unchanged
     * @param int|null          $allocatedInventoryAlt  Alloced Inventory Change qty, qty that represents the
     *                                                  actual change
     * @param User|null         $user                   User who triggered the change, if left null,
     *                                                  it is automatically assigned to current one
     * @param mixed|null        $subject                Any entity that should be associated to this operation
     *
     * @throws \Exception
     * @return bool
     */
    public function updateInventoryLevel(
        InventoryItem $item,
        $trigger,
        $inventory = null,
        $inventoryAlt = null,
        $allocatedInventory = null,
        $allocatedInventoryAlt = null,
        User $user = null,
        $subject = null
    ) {
        if (($inventory === null) && ($allocatedInventory === null)) {
            return false;
        }

        if (($item->getStock() === $inventory) && ($item->getAllocatedStock() === $allocatedInventory)) {
            return false;
        }

        if ($inventory === null) {
            $inventory = $item->getStock();
        }

        if ($inventoryAlt === null) {
            $inventoryAlt = 0;
        }

        if ($allocatedInventory === null) {
            $allocatedInventory = $item->getAllocatedStock();
        }

        if ($allocatedInventoryAlt === null) {
            $allocatedInventoryAlt = 0;
        }

        try {
            $item->changeCurrentLevel(new StockLevel(
                $item,
                $inventory,
                $inventoryAlt,
                $allocatedInventory,
                $allocatedInventoryAlt,
                $trigger,
                $user,
                $subject
            ));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return true;
    }

    /**
     * Validate the data structure of the items to be updated
     * @param InventoryUpdateContext $context
     * @return bool
     */
    private function validateItems($context)
    {
        $items = $context->getItems();
        foreach ($items as $item) {
            if (!is_array($item)) {
                return false;
            }

            if (!array_key_exists('item', $item)) {
                return false;
            }

            if (!array_key_exists('qty', $item)) {
                return false;
            }

            if (!array_key_exists('allocatedQty', $item)) {
                return false;
            }
        }

        return true;
    }
}
