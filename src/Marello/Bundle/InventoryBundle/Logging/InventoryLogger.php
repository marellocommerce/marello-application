<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;

class InventoryLogger
{
    /** @var Registry */
    protected $doctrine;

    /** @var EntityManager */
    protected $manager = null;

    /**
     * InventoryLogger constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param InventoryItem[]|InventoryItem $items
     * @param string                        $trigger
     * @param \Closure|null                 $modifyLog
     *
     * @throws \Exception
     */
    public function log($items, $trigger, \Closure $modifyLog = null)
    {
        if (!is_array($items)) {
            $items = [$items];
        }

        $uow = $this->manager()->getUnitOfWork();
        $uow->computeChangeSets();

        foreach ($items as $item) {
            /*
             * Create new log instance.
             */
            if (UnitOfWork::STATE_NEW === $uow->getEntityState($item, UnitOfWork::STATE_NEW)) {
                $log = $this->getNewItemLog($item);
            } else {
                $log = $this->getModifiedItemLog($item);
            }

            /*
             * If no log has been returned, it means that provided item was not changed so there is nothing to log.
             */
            if (!$log) {
                continue;
            }

            /*
             * Set action type that triggered log.
             */
            $log->setActionType($trigger);

            /*
             * If log needs to be modified, do it.
             */
            if ($modifyLog) {
                call_user_func($modifyLog, $log);
            }

            /*
             * Logs are not flushed, they should be part of a transaction logic.
             */
            $this->manager()->persist($log);
        }
    }

    /**
     * Creates inventory log for new inventory item.
     *
     * @param InventoryItem $inventoryItem
     *
     * @return InventoryLog|null Null if item has quantity values of 0.
     */
    protected function getNewItemLog(InventoryItem $inventoryItem)
    {
        if (!$inventoryItem->getQuantity() && $inventoryItem->getAllocatedQuantity()) {
            return null;
        }

        return (new InventoryLog($inventoryItem))
            ->setNewQuantity($inventoryItem->getQuantity())
            ->setNewAllocatedQuantity($inventoryItem->getAllocatedQuantity());
    }

    /**
     * Creates inventory log for modified inventory item.
     *
     * @param InventoryItem $item
     *
     * @return InventoryLog|null Null if item quantities were not modified.
     */
    protected function getModifiedItemLog(InventoryItem $item)
    {
        $uow       = $this->manager()->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($item);

        $log = new InventoryLog($item);

        /*
         * Check if any of quantities were changed.
         */
        $quantityChanged          = array_key_exists('quantity', $changeSet);
        $allocatedQuantityChanged = array_key_exists('allocatedQuantity', $changeSet);

        /*
         * If no quantity was changed, return null and do not log any changes.
         */
        if (!$quantityChanged && !$allocatedQuantityChanged) {
            return null;
        }

        if ($quantityChanged) {
            $log
                ->setOldQuantity($changeSet['quantity'][0])
                ->setNewQuantity($changeSet['quantity'][1]);
        } else {
            $log
                ->setNewQuantity($item->getQuantity())
                ->setOldQuantity($item->getQuantity());
        }

        if ($allocatedQuantityChanged) {
            $log
                ->setOldAllocatedQuantity($changeSet['allocatedQuantity'][0])
                ->setNewAllocatedQuantity($changeSet['allocatedQuantity'][0]);
        } else {
            $log
                ->setOldAllocatedQuantity($item->getAllocatedQuantity())
                ->setNewAllocatedQuantity($item->getAllocatedQuantity());
        }

        return $log;
    }

    /**
     * @return EntityManager
     */
    protected function manager()
    {
        if ($this->manager) {
            return $this->manager;
        }

        return $this->manager = $this->doctrine
            ->getManager();
    }
}
