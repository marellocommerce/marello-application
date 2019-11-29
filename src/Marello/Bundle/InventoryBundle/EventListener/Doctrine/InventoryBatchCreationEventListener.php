<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Factory\InventoryBatchFromInventoryLevelFactory;

class InventoryBatchCreationEventListener
{
    /**
     * @var InventoryBatch[]
     */
    private $inventoryBatches = [];

    /**
     * @param InventoryItem $inventoryItem
     */
    public function postPersist(InventoryItem $inventoryItem)
    {
        if ($inventoryItem->isEnableBatchInventory() === true &&
            count($inventoryItem->getInventoryLevels()->toArray()) > 0) {
            foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                $this->inventoryBatches[] =
                    InventoryBatchFromInventoryLevelFactory::createInventoryBatch($inventoryLevel);
            }
        }
    }

    /**
     * @param InventoryItem $inventoryItem
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(InventoryItem $inventoryItem, PreUpdateEventArgs $args)
    {
        $changeSet = $args->getEntityChangeSet();
        if (count($changeSet) > 0 &&
            isset($changeSet['enableBatchInventory']) &&
            $changeSet['enableBatchInventory'][1] === true&&
            count($inventoryItem->getInventoryLevels()->toArray()) > 0) {
            foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                $this->inventoryBatches[] =
                    InventoryBatchFromInventoryLevelFactory::createInventoryBatch($inventoryLevel);
            }
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!empty($this->inventoryBatches)) {
            $entityManager = $args->getEntityManager();
            foreach ($this->inventoryBatches as $inventoryBatch) {
                $entityManager->persist($inventoryBatch);
            }
            $this->inventoryBatches = [];
            $entityManager->flush();
        }
    }
}