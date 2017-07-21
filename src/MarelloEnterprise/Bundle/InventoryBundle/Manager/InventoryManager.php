<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager as BaseInventoryManager;

class InventoryManager extends BaseInventoryManager
{
    /**
     * Update inventory items based of context and calculate new inventory level
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryLevels(InventoryUpdateContext $context)
    {
        if (!$this->contextValidator->validateContext($context)) {
            throw new \Exception('Context structure not valid.');
        }

        $inventory = null;
        $allocatedStock = null;
        if ($context->getInventory()) {
            $inventory = $context->getInventory();
        }

        if ($context->getAllocatedInventory()) {
            $allocatedStock = $context->getAllocatedInventory();
        }

        /** @var InventoryLevel $level */
        $level = $this->getInventoryLevel($context);
        if (!$level) {
            $level = new InventoryLevel();
            $level->setWarehouse($this->getWarehouse($context));
            /** @var InventoryItem $item */
            $item = $this->getInventoryItem($context);
            $item->addInventoryLevel($level);
        }

        $this->updateInventoryLevel(
            $level,
            $context->getChangeTrigger(),
            $inventory,
            $context->getInventory(),
            $allocatedStock,
            $context->getAllocatedInventory(),
            $context->getUser(),
            $context->getRelatedEntity()
        );
    }

    /**
     * Get warehouse from context or get default
     * @param InventoryUpdateContext $context
     * @return Warehouse
     */
    private function getWarehouse(InventoryUpdateContext $context)
    {
        if ($warehouse = $context->getValue('warehouse')) {
            return $warehouse;
        }

        /** @var WarehouseRepository $repo */
        $repo = $this->doctrineHelper->getEntityRepositoryForClass(Warehouse::class);
        return $repo->getDefault();
    }
}
