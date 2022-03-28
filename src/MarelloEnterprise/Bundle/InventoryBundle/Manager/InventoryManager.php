<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Factory\InventoryBatchFromInventoryLevelFactory;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager as BaseInventoryManager;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;

class InventoryManager extends BaseInventoryManager
{
    /**
     * Update inventory items based of context and calculate new inventory level
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryLevel(InventoryUpdateContext $context)
    {
        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::INVENTORY_UPDATE_BEFORE,
            new InventoryUpdateEvent($context)
        );

        if (!$this->contextValidator->validateContext($context)) {
            throw new \Exception('Context structure not valid.');
        }
        /** @var InventoryItem $item */
        $item = $this->getInventoryItem($context);
        /** @var InventoryLevel $level */
        $level = $this->getInventoryLevel($context);
        if (!$level) {
            $level = new InventoryLevel();
            $level
                ->setWarehouse($this->getWarehouse($context))
                ->setOrganization($context->getProduct()->getOrganization());
            $item->addInventoryLevel($level);
        }
        $warehouseType = $level->getWarehouse()->getWarehouseType()->getName();
        if ($item && $item->isEnableBatchInventory() &&
            $warehouseType !== WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
            if (empty($context->getInventoryBatches()) && (
                $context->getRelatedEntity() instanceof PurchaseOrder ||
                $context->getChangeTrigger() === 'import')
            ) {
                $batch = InventoryBatchFromInventoryLevelFactory::createInventoryBatch($level);
                $batch->setQuantity(0);
                $batchInventory = ($batch->getQuantity() + $context->getInventory());
                $updatedBatch = $this->updateInventoryBatch($batch, $batchInventory);
                $context->setInventoryBatches([$updatedBatch]);
            } else {
                $updatedBatches = [];
                foreach ($context->getInventoryBatches() as $batchData) {
                    /** @var InventoryBatch $batch */
                    $batch = $batchData['batch'];
                    $batch->setInventoryLevel($level);
                    $qty = $batchData['qty'];
                    $batchInventory = ($batch->getQuantity() + $qty);
                    $updatedBatches[] = $this->updateInventoryBatch($batch, $batchInventory);
                }
                $context->setInventoryBatches($updatedBatches);
            }
        }

        $inventory = null;
        $allocatedInventory = null;
        if ($context->getInventory()) {
            $inventory = ($level->getInventoryQty() + $context->getInventory());
        }

        if ($context->getAllocatedInventory()) {
            $allocatedInventory = ($level->getAllocatedInventoryQty() + $context->getAllocatedInventory());
        }

        if ($isManagedInventory = $context->getValue('isInventoryManaged')) {
            $level->setManagedInventory($isManagedInventory);
        }
        /** @var InventoryBatch[] $updatedBatches */
        $updatedBatches = $context->getInventoryBatches();
        if (count($updatedBatches) === 1) {
            $level->addInventoryBatch($updatedBatches[0]);
        }
        $updatedLevel = $this->updateInventory($level, $inventory, $allocatedInventory);
        $context->setInventoryLevel($updatedLevel);

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::INVENTORY_UPDATE_AFTER,
            new InventoryUpdateEvent($context)
        );
        if (!empty($context->getInventoryBatches())) {
            // for some reason multiple batches are not saved when this flush is not triggered..
            // which causes issues when replenishing multiple batches :/ (can't complete the replenishment order)
            $this->doctrineHelper
                ->getEntityManagerForClass(InventoryBatch::class)
                ->flush();
        }
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
        return $repo->getDefault($this->aclHelper);
    }

    /**
     * @param InventoryUpdateContext $context
     * @return null|object
     */
    protected function getInventoryLevel(InventoryUpdateContext $context)
    {
        if ($context->getInventoryLevel()) {
            return $context->getInventoryLevel();
        }

        $inventoryItem = $this->getInventoryItem($context);
        $warehouse = $this->getWarehouse($context);
        $repo = $this->doctrineHelper->getEntityRepositoryForClass(InventoryLevel::class);
        $level = $repo->findOneBy(
            [
                'inventoryItem' => $inventoryItem,
                'warehouse'     => $warehouse
            ]
        );

        return $level;
    }
}
