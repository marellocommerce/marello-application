<?php

namespace Marello\Bundle\InventoryBundle\Manager;

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

    /**
     * @deprecated use updateInventoryLevel instead
     * Update inventory items based of context and calculate new inventory level
     * @param InventoryUpdateContext $context
     * @throws \Exception
     */
    public function updateInventoryItems(InventoryUpdateContext $context)
    {
        $this->updateInventoryLevel($context);
    }

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

        $updatedLevel = $this->updateInventory($level, $inventory, $allocatedInventory);
        $context->setInventoryLevel($updatedLevel);

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::INVENTORY_UPDATE_AFTER,
            new InventoryUpdateEvent($context)
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

    /**
     * Sets the event dispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
