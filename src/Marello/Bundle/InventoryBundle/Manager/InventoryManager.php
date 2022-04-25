<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Factory\InventoryBatchFromInventoryLevelFactory;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextValidator;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;

class InventoryManager implements InventoryManagerInterface
{
    /** @var DoctrineHelper $doctrineHelper*/
    protected $doctrineHelper;

    /** @var InventoryUpdateContextValidator $contextValidator */
    protected $contextValidator;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /** @var AclHelper $aclHelper */
    protected $aclHelper;

    /**
     * Update inventory items based of context and calculate new inventory level
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryLevel(InventoryUpdateContext $context)
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
                $updatedBatch = $this->updateInventoryBatch($batch, $batchInventory);
                $context->setInventoryBatches([$updatedBatch]);
            } else {
                $updatedBatches = [];
                foreach ($context->getInventoryBatches() as $batchData) {
                    /** @var InventoryBatch $batch */
                    $batch = $batchData['batch'];
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
        $level->setManagedInventory($context->getValue('isInventoryManaged'));
        /** @var InventoryBatch[] $updatedBatches */
        $updatedBatches = $context->getInventoryBatches();
        if (count($updatedBatches) === 1 && $updatedBatches[0]->getId() === null) {
            $level->addInventoryBatch($updatedBatches[0]);
        }
        $updatedLevel = $this->updateInventory($level, $inventory, $allocatedInventory);
        $context->setInventoryLevel($updatedLevel);

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::INVENTORY_UPDATE_AFTER
        );
    }
    
    /**
     * @param InventoryLevel    $level                  InventoryLevel to be updated
     * @param int|null          $inventory              New inventory or null if it should remain unchanged
     * @param int|null          $allocatedInventory     New allocated inventory or null if it should remain unchanged
     *                                                  actual change
     * @throws \Exception
     * @return InventoryLevel
     */
    protected function updateInventory(
        InventoryLevel $level,
        $inventory = null,
        $allocatedInventory = null
    ) {
        if (($inventory === null) && ($allocatedInventory === null)) {
            return $level;
        }

        if (($level->getInventoryQty() === $inventory) &&
            ($level->getAllocatedInventoryQty() === $allocatedInventory)) {
            return $level;
        }

        if ($inventory === null) {
            $inventory = $level->getInventoryQty();
        }

        if ($allocatedInventory === null) {
            $allocatedInventory = $level->getAllocatedInventoryQty();
        }

        try {
            $level
                ->setInventoryQty($inventory)
                ->setAllocatedInventoryQty($allocatedInventory);

            $em = $this->doctrineHelper->getEntityManager($level);
            $em->persist($level);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $level;
    }

    /**
     * @param InventoryBatch $batch
     * @param int|null $quantity
     * @throws \Exception
     * @return InventoryBatch
     */
    protected function updateInventoryBatch(
        InventoryBatch $batch,
        $quantity = null
    ) {
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
        $repo = $this->doctrineHelper->getEntityRepositoryForClass(InventoryLevel::class);
        $level = $repo->findOneBy(['inventoryItem' => $inventoryItem]);

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

    /**
     * Sets the context validator
     * @param InventoryUpdateContextValidator $validator
     */
    public function setContextValidator(InventoryUpdateContextValidator $validator)
    {
        $this->contextValidator = $validator;
    }

    /**
     * Sets the doctrine helper
     *
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Sets the event dispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setAclHelper(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }
}
