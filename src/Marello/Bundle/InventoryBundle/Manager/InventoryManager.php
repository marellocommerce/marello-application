<?php

namespace Marello\Bundle\InventoryBundle\Manager;

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
        if (!$this->contextValidator->validateItems($context)) {
            throw new \Exception('Item structure not valid.');
        }

        $items = $context->getItems();
        foreach ($items as $data) {

            /** @var ProductInterface $product */
            $product = $data['item'];
            $inventoryItem = $context->getInventoryItem() ? $context->getInventoryItem() : $this->getInventoryItem($product);

            if (!$inventoryItem) {
                continue;
            }

            $inventory = null;
            $allocatedStock = null;
            if ($context->getInventory()) {
                $inventory = ($inventoryItem->getStock() + $context->getInventory());
            }

            if ($context->getAllocatedInventory()) {
                $allocatedStock = ($inventoryItem->getAllocatedStock() + $context->getAllocatedInventory());
            }

            $this->updateInventoryLevel(
                $inventoryItem,
                $context->getChangeTrigger(),
                $inventory,
                $context->getInventory(),
                $allocatedStock,
                $context->getAllocatedInventory(),
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
            // find inventory level based on the warehouse
            /** @var InventoryLevel $level */
            $level = $this->getInventoryLevel($item);

            if (!$level) {
                $level = new InventoryLevel(
                    $item,
                    $inventory,
                    $inventoryAlt,
                    $allocatedInventory,
                    $allocatedInventoryAlt,
                    $trigger,
                    $user,
                    $subject
                );
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $item->addInventoryLevel($level);
        return true;
    }

    /**
     * @param ProductInterface $product
     * @return null|object
     */
    private function getInventoryItem(ProductInterface $product)
    {
        $repo = $this->doctrineHelper->getEntityRepositoryForClass(InventoryItem::class);
        return $repo->findOneBy(['product' => $product]);
    }

    /**
     * @param InventoryItem $inventoryItem
     * @return null|object
     */
    private function getInventoryLevel(InventoryItem $inventoryItem)
    {
        $repo = $this->doctrineHelper->getEntityRepositoryForClass(InventoryLevel::class);
        $level = $repo->findOneBy(['inventoryItem' => $inventoryItem]);
        if (!$level && $inventoryItem->hasInventoryLevels()) {
            $level = $inventoryItem->getInventoryLevels()->first();
        }

        return $level;
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
