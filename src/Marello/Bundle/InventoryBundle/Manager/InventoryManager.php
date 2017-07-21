<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevelLogRecord;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextValidator;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;

class InventoryManager implements InventoryManagerInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var InventoryUpdateContextValidator $contextValidator */
    private $contextValidator;

    /**
     * @deprecated use updateInventoryLevels instead
     * Update inventory items based of context and calculate new inventory level
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryItems(InventoryUpdateContext $context)
    {
        $this->updateInventoryLevels($context);
    }

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
            $level->setWarehouse($this->getWarehouse());
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
     * @param InventoryLevel    $level                  InventoryLevel to be updated
     * @param string            $trigger                Action that triggered the change
     * @param int|null          $inventory              New inventory or null if it should remain unchanged
     * @param int|null          $inventoryAlt           Inventory Change qty, qty that represents the actual change
     * @param int|null          $allocatedInventory     New allocated inventory or null if it should remain unchanged
     * @param int|null          $allocatedInventoryAlt  Allocated Inventory Change qty, qty that represents the
     *                                                  actual change
     * @param User|null         $user                   User who triggered the change, if left null,
     *                                                  it is automatically assigned to current one
     * @param mixed|null        $subject                Any entity that should be associated to this operation
     *
     * @throws \Exception
     * @return bool
     */
    protected function updateInventoryLevel(
        InventoryLevel $level,
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

        if (($level->getInventoryQty() === $inventory) && ($level->getAllocatedInventoryQty() === $allocatedInventory)) {
            return false;
        }

        if ($inventory === null) {
            $inventory = $level->getInventoryQty();
        } else {
            $inventory = ($inventory + $level->getInventoryQty());
        }

        if ($inventoryAlt === null) {
            $inventoryAlt = 0;
        }

        if ($allocatedInventory === null) {
            $allocatedInventory = $level->getAllocatedInventoryQty();
        } else {
            $allocatedInventory = ($allocatedInventory + $level->getAllocatedInventoryQty());
        }

        if ($allocatedInventoryAlt === null) {
            $allocatedInventoryAlt = 0;
        }

        try {
            $level
                ->setInventoryQty($inventory)
                ->setAllocatedInventoryQty($allocatedInventory);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $this->createLogRecord(
            $level,
            $inventoryAlt,
            $allocatedInventoryAlt,
            $trigger,
            $user,
            $subject
        );
        return true;
    }

    /**
     * @param InventoryLevel $level
     * @param $inventoryAlt
     * @param $allocatedInventoryAlt
     * @param $trigger
     * @param $user
     * @param $subject
     */
    protected function createLogRecord(
        InventoryLevel $level,
        $inventoryAlt,
        $allocatedInventoryAlt,
        $trigger,
        $user,
        $subject
    ) {
        $record = new InventoryLevelLogRecord(
            $level,
            $inventoryAlt,
            $allocatedInventoryAlt,
            $trigger,
            $user,
            $subject
        );

        $em = $this->doctrineHelper->getEntityManager($record);
        $em->persist($level);
        $em->persist($record);
    }

    /**
     * @param InventoryUpdateContext $context
     * @return null|object
     */
    private function getInventoryItem(InventoryUpdateContext $context)
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
    private function getInventoryLevel(InventoryUpdateContext $context)
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
        return $repo->getDefault();
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
}
