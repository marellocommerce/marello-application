<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextValidator;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\InventoryBundle\Factory\InventoryBatchFromInventoryLevelFactory;

class InventoryManager implements InventoryManagerInterface
{
    /** @var DoctrineHelper $doctrineHelper*/
    protected DoctrineHelper $doctrineHelper;

    /** @var InventoryUpdateContextValidator $contextValidator */
    protected InventoryUpdateContextValidator $contextValidator;

    /** @var InventoryLevelCalculator $inventoryLevelCalculator */
    protected InventoryLevelCalculator $inventoryLevelCalculator;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected EventDispatcherInterface $eventDispatcher;

    /** @var AclHelper $aclHelper */
    protected AclHelper $aclHelper;

    public function __construct(
        InventoryUpdateContextValidator $contextValidator,
        InventoryLevelCalculator $inventoryLevelCalculator,
        DoctrineHelper $doctrineHelper,
        AclHelper $aclHelper,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->contextValidator = $contextValidator;
        $this->inventoryLevelCalculator = $inventoryLevelCalculator;
        $this->doctrineHelper = $doctrineHelper;
        $this->aclHelper = $aclHelper;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Update inventory items based of context and calculate new inventory level
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryLevel(InventoryUpdateContext $context): void
    {
        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::INVENTORY_UPDATE_BEFORE
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
                ->setWarehouse($this->getWarehouse())
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
                $isNewBatch = !$batch->getId();
                $updatedBatch = $this->updateInventoryBatch($batch, $batchInventory);
                $context->setInventoryBatches(
                    [[
                        'batch' => $updatedBatch,
                        'qty' => $batchInventory,
                        'isNew' => $isNewBatch
                    ]]
                );
            } else {
                $updatedBatches = [];
                foreach ($context->getInventoryBatches() as $batchData) {
                    /** @var InventoryBatch $batch */
                    $batch = $batchData['batch'];
                    $qty = $batchData['qty'];
                    $batchInventory = ($batch->getQuantity() + $qty);
                    $isNewBatch = !$batch->getId();
                    $updatedBatches[] = [
                        'batch'=> $this->updateInventoryBatch($batch, $batchInventory),
                        'qty' => $batchData['qty'],
                        'isNew' => $isNewBatch
                    ];
                }

                $context->setInventoryBatches($updatedBatches);
            }
        }

        $inventory = null;
        $allocatedInventory = null;
        if ($context->getInventory()) {
            $inventory = ($level->getInventoryQty() + $context->getInventory());
            if ($warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL
                && (!$level->isManagedInventory())) {
                $inventory = 0;
            }
        }

        if ($level->getInventoryItem()->isEnableBatchInventory()) {
            $batches = $level->getInventoryBatches()->toArray();
            $inventory = $this->inventoryLevelCalculator->calculateBatchInventoryLevelQty($batches);
            $updatedBatchInventoryTotal = $this
                ->inventoryLevelCalculator
                ->calculateBatchInventoryLevelQty($context->getInventoryBatches());
            foreach ($context->getInventoryBatches() as $batch) {
                if ($batch['isNew']) {
                    $inventory += $updatedBatchInventoryTotal;
                }
            }
        }

        if ($context->getAllocatedInventory()) {
            $allocatedInventory = ($level->getAllocatedInventoryQty() + $context->getAllocatedInventory());
        }
        $level->setManagedInventory($context->getValue('isInventoryManaged'));
        /** @var InventoryBatch[] $updatedBatches */
        $updatedBatches = $context->getInventoryBatches();
        if (count($updatedBatches) === 1 && $updatedBatches[0]['batch']->getId() === null) {
            $level->addInventoryBatch($updatedBatches[0]['batch']);
        }
        $updatedLevel = $this->updateInventory($level, $inventory, $allocatedInventory);
        $context->setInventoryLevel($updatedLevel);

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::INVENTORY_UPDATE_AFTER
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
     * @param InventoryLevel    $inventoryLevel         InventoryLevel to be updated
     * @param int|null          $inventory              New inventory or null if it should remain unchanged
     * @param int|null          $allocatedInventory     New allocated inventory or null if it should remain unchanged

     * @throws \Exception
     * @return InventoryLevel
     */
    public function updateInventory(
        InventoryLevel $inventoryLevel,
        int $inventory = null,
        int $allocatedInventory = null
    ): InventoryLevel {
        if (($inventory === null) && ($allocatedInventory === null)) {
            return $inventoryLevel;
        }

        if (($inventoryLevel->getInventoryQty() === $inventory) &&
            ($inventoryLevel->getAllocatedInventoryQty() === $allocatedInventory)) {
            return $inventoryLevel;
        }

        if ($inventory === null) {
            $inventory = $inventoryLevel->getInventoryQty();
        }

        if ($allocatedInventory === null) {
            $allocatedInventory = $inventoryLevel->getAllocatedInventoryQty();
        }

        try {
            $inventoryLevel
                ->setInventoryQty($inventory)
                ->setAllocatedInventoryQty($allocatedInventory);

            $em = $this->doctrineHelper->getEntityManager($inventoryLevel);
            $em->persist($inventoryLevel);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $inventoryLevel;
    }

    /**
     * @param InventoryBatch $batch InventoryBatch to be updated
     * @param int|null $quantity New batch quantity or null if it should remain unchanged
     * @throws \Exception
     * @return InventoryBatch
     */
    public function updateInventoryBatch(
        InventoryBatch $batch,
        $quantity = null
    ): InventoryBatch {
        if ($quantity === null) {
            return $batch;
        }

        if ($batch->getQuantity() === $quantity) {
            return $batch;
        }

        try {
            $batch
                ->setQuantity($quantity);

            $em = $this->doctrineHelper->getEntityManager($batch);
            $em->persist($batch);
            $em->flush($batch);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $batch;
    }

    /**
     * @param $entity
     * @return int
     */
    public function getExpectedInventoryTotal($entity)
    {
        $total = 0;
        $purchaseOrderItems = $this->doctrineHelper->getEntityRepositoryForClass(PurchaseOrderItem::class)
            ->getExpectedItemsByProduct($entity->getProduct());
        /** @var PurchaseOrderItem $purchaseOrderItem */
        foreach ($purchaseOrderItems as $purchaseOrderItem) {
            $total += $purchaseOrderItem->getOrderedAmount() - $purchaseOrderItem->getReceivedAmount();
        }

        return $total;
    }

    public function getExpiredSellByDateTotal(InventoryItem $entity): int
    {
        $total = 0;
        $currentDateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        foreach ($entity->getInventoryLevels() as $inventoryLevel) {
            foreach ($inventoryLevel->getInventoryBatches() as $batch) {
                if ($batch->getSellByDate() && $batch->getSellByDate() < $currentDateTime) {
                    $total += $batch->getQuantity();
                }
            }
        }

        return $total;
    }

    /**
     * @param InventoryUpdateContext $context
     * @return null|object
     */
    protected function getInventoryItem(InventoryUpdateContext $context)
    {
        if ($inventoryItem = $context->getInventoryItem()) {
            return $inventoryItem;
        }

        /** @var ProductInterface $product */
        $product = $context->getProduct();
        $repo = $this->doctrineHelper->getEntityRepositoryForClass(InventoryItem::class);
        return $repo->findOneBy(['product' => $product]);
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
        $warehouse = $this->getWarehouse();
        if ($context->getValue('warehouse')) {
            $warehouse = $context->getValue('warehouse');
        }
        $repo = $this->doctrineHelper->getEntityRepositoryForClass(InventoryLevel::class);
        $level = $repo->findOneBy(['inventoryItem' => $inventoryItem, 'warehouse' => $warehouse]);
        if (!$level && $inventoryItem->hasInventoryLevels()) {
            $level = $inventoryItem->getInventoryLevels()->first();
        }

        return $level;
    }

    /**
     * Get default warehouse
     * @return Warehouse
     */
    private function getWarehouse()
    {
        /** @var WarehouseRepository $repo */
        $repo = $this->doctrineHelper->getEntityRepositoryForClass(Warehouse::class);
        return $repo->getDefault($this->aclHelper);
    }
}
