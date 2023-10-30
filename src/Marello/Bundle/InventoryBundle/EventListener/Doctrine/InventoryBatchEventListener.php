<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Marello\Bundle\InventoryBundle\Manager\InventoryManagerInterface;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\InventoryBundle\Factory\InventoryBatchFromInventoryLevelFactory;

class InventoryBatchEventListener
{
    /** @var array */
    protected array $mapping = [
        'fields' => [
            'expirationDate'
        ]
    ];

    /** @var InventoryManagerInterface $inventoryManager */
    private InventoryManagerInterface $inventoryManager;

    /** @var InventoryLevelCalculator $inventoryCalculator */
    private InventoryLevelCalculator $inventoryCalculator;

    /**
     * @var InventoryBatch[]
     */
    private array $inventoryBatches = [];

    /**
     * @param InventoryManagerInterface $inventoryManager
     * @param InventoryLevelCalculator $calculator
     */
    public function __construct(
        InventoryManagerInterface $inventoryManager,
        InventoryLevelCalculator $calculator
    ) {
        $this->inventoryManager = $inventoryManager;
        $this->inventoryCalculator = $calculator;
    }

    /**
     * @param InventoryItem $inventoryItem
     * @return void
     */
    public function postPersist(InventoryItem $inventoryItem): void
    {
        if ($inventoryItem->isEnableBatchInventory() === true &&
            count($inventoryItem->getInventoryLevels()->toArray()) > 0) {
            foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                $warehouseType = $inventoryLevel->getWarehouse()->getWarehouseType()->getName();
                if ($warehouseType !== WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL) {
                    $this->inventoryBatches[] =
                        InventoryBatchFromInventoryLevelFactory::createInventoryBatch($inventoryLevel);
                }
            }
        }
    }

    /**
     * @param InventoryItem $inventoryItem
     * @param PreUpdateEventArgs $args
     * @return void
     */
    public function preUpdate(InventoryItem $inventoryItem, PreUpdateEventArgs $args): void
    {
        $changeSet = $args->getEntityChangeSet();
        if (count($changeSet) > 0 &&
            isset($changeSet['enableBatchInventory']) &&
            $changeSet['enableBatchInventory'][1] === true &&
            count($inventoryItem->getInventoryLevels()->toArray()) > 0) {
            foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                $this->inventoryBatches[] =
                    InventoryBatchFromInventoryLevelFactory::createInventoryBatch($inventoryLevel);
            }
        }
    }

    /**
     * @param InventoryBatch $inventoryBatch
     * @param PreUpdateEventArgs $args
     * @return void
     * @throws \Exception
     */
    public function inventoryBatchPreUpdate(InventoryBatch $inventoryBatch, PreUpdateEventArgs $args): void
    {
        $changeSet = $args->getEntityChangeSet();
        $changedTrackedFieldValues = \array_intersect(
            $this->mapping['fields'],
            \array_keys($changeSet)
        );
        if ($changedTrackedFieldValues) {
            $batches = $inventoryBatch->getInventoryLevel()->getInventoryBatches()->toArray();
            $batchInventory = $this->inventoryCalculator->calculateBatchInventoryLevelQty($batches);
            $this->inventoryManager->updateInventory($inventoryBatch->getInventoryLevel(), $batchInventory);
            $this->inventoryBatches = array_merge($this->inventoryBatches, $batches);
        }
    }

    /**
     * @param PostFlushEventArgs $args
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if (!empty($this->inventoryBatches)) {
            $entityManager = $args->getObjectManager();
            foreach ($this->inventoryBatches as $inventoryBatch) {
                $entityManager->persist($inventoryBatch);
            }
            $this->inventoryBatches = [];
            $entityManager->flush();
        }
    }
}
