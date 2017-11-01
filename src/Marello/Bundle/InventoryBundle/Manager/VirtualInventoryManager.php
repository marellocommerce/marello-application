<?php

namespace Marello\Bundle\InventoryBundle\Manager;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevelLogRecord;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextValidator;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UserBundle\Entity\User;

class VirtualInventoryManager
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var InventoryUpdateContextValidator
     */
    protected $contextValidator;

    /**
     * Update inventory items based of context and calculate new inventory level
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateVirtualInventoryLevels(InventoryUpdateContext $context)
    {
        if (!$this->contextValidator->validateContext($context)) {
            throw new \Exception('Context structure not valid.');
        }

        /** @var InventoryLevel $level */
        $level = $this->getInventoryLevel($context);
        if (!$level) {
            $level = new InventoryLevel();
            $level
                ->setWarehouse($this->getWarehouse())
                ->setOrganization($context->getProduct()->getOrganization());
            
            /** @var InventoryItem $item */
            $item = $this->getInventoryItem($context);
            $item->addInventoryLevel($level);
        }

        $inventory = null;
        $allocatedInventory = null;
        if ($context->getInventory()) {
            $inventory = ($level->getInventoryQty() + $context->getInventory());
        }

        if ($context->getAllocatedInventory()) {
            $allocatedInventory = ($level->getAllocatedInventoryQty() + $context->getAllocatedInventory());
        }

        $this->updateInventoryLevel(
            $level,
            $context->getChangeTrigger(),
            $inventory,
            $context->getInventory(),
            $allocatedInventory,
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

        if (($level->getInventoryQty() === $inventory) &&
            ($level->getAllocatedInventoryQty() === $allocatedInventory)) {
            return false;
        }

        if ($inventory === null) {
            $inventory = $level->getInventoryQty();
        }

        if ($inventoryAlt === null) {
            $inventoryAlt = 0;
        }

        if ($allocatedInventory === null) {
            $allocatedInventory = $level->getAllocatedInventoryQty();
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

        return true;
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
