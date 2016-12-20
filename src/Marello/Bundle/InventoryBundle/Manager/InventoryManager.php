<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Entity\StockLevel;

class InventoryManager implements InventoryManagerInterface
{
    public function updateInventoryItems(InventoryUpdateContext $context)
    {
        if (!$this->validateItems($context)) {
            throw new \Exception('Item structure not valid.');
        }

        $items = $context->getItems();
        foreach ($items as $item) {
            $this->setInventoryLevel(
                $item,
                $context->getChangeTrigger(),
                $context->getStock(),
                $context->getAllocatedStock(),
                $context->getUser(),
                $context->getRelatedEntity()
            );
        }
    }

    /**
     * @param InventoryItem $item        InventoryItem to be updated
     * @param string     $trigger        Action that triggered the change
     * @param int|null   $stock          New stock or null if it should remain unchanged
     * @param int|null   $allocatedStock New allocated stock or null if it should remain unchanged
     * @param User|null  $user           User who triggered the change, if left null, it is automatically assigned ot
     *                                   current one
     * @param mixed|null $subject        Any entity that should be associated to this operation
     *
     * @return $this
     */
    protected function setInventoryLevel(InventoryItem $item, $trigger, $stock = null, $allocatedStock = null, User $user = null, $subject = null)
    {
        if (($stock === null) && ($allocatedStock === null)) {
            return $this;
        }

        if (($item->getStock() === $stock) && ($item->getAllocatedStock() === $allocatedStock)) {
            return $this;
        }

        return $this->changeCurrentLevel(new StockLevel(
            $item,
            $stock === null ? $item->getStock() : $stock,
            $allocatedStock === null ? $item->getAllocatedStock() : $allocatedStock,
            $trigger,
            $user,
            $subject
        ));
    }

    private function validateItems($context)
    {
        $items = $context->getItems();
        if (!is_array($items)) {
            return false;
        }

        if (!in_array('item', $items)) {
            return false;
        }

        if (!in_array('qty', $items)) {
            return false;
        }

        if (!in_array('allocatedQty', $items)) {
            return false;
        }

        return true;
    }
}
