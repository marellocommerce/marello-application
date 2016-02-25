<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Closure;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;

class InventoryLogger
{
    /** @var Registry */
    protected $doctrine;

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
     * Creates inventory log with given values, set in $setValues callback.
     * WARNING: Log is not persisted if old and new quantities are equal.
     *
     * @param InventoryItem $item
     * @param string        $trigger
     * @param Closure       $setValues Closure used to set log values. Takes InventoryLog as single parameter.
     */
    public function directLog(InventoryItem $item, $trigger, Closure $setValues)
    {
        $log = new InventoryLog($item, $trigger);

        call_user_func($setValues, $log);

        /*
         * If new and old values are the same, do nothing.
         */
        if (($log->getOldQuantity() === $log->getNewQuantity()) &&
            ($log->getOldAllocatedQuantity() === $log->getNewAllocatedQuantity())
        ) {
            return;
        }

        $this->manager()->persist($log);
    }

    /**
     * Logs inventory item changes based on computed doctrine change set.
     * Checks if there are any changes for all given items.
     *
     * @param InventoryItem[]|InventoryItem $items
     * @param string                        $trigger
     * @param Closure|null                  $modifyLog
     *
     * @throws \Exception
     */
    public function log($items, $trigger, Closure $modifyLog = null)
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
                $log = $this->getNewItemLog($item, $trigger);
            } else {
                $log = $this->getModifiedItemLog($item, $trigger);
            }

            /*
             * If no log has been returned, it means that provided item was not changed so there is nothing to log.
             */
            if (!$log) {
                continue;
            }

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
     * @param string        $trigger
     *
     * @return InventoryLog|null Null if item has quantity values of 0.
     */
    protected function getNewItemLog(InventoryItem $inventoryItem, $trigger)
    {
        if (!$inventoryItem->getQuantity() && $inventoryItem->getAllocatedQuantity()) {
            return null;
        }

        return (new InventoryLog($inventoryItem, $trigger))
            ->setOldQuantity(0)
            ->setOldAllocatedQuantity(0);
    }

    /**
     * Creates inventory log for modified inventory item.
     *
     * @param InventoryItem $item
     * @param string        $trigger
     *
     * @return InventoryLog|null Null if item quantities were not modified.
     */
    protected function getModifiedItemLog(InventoryItem $item, $trigger)
    {
        $uow       = $this->manager()->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($item);

        $log = new InventoryLog($item, $trigger);

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
        }

        if ($allocatedQuantityChanged) {
            $log
                ->setOldAllocatedQuantity($changeSet['allocatedQuantity'][0])
                ->setNewAllocatedQuantity($changeSet['allocatedQuantity'][1]);
        }

        return $log;
    }

    /**
     * @return EntityManager
     */
    protected function manager()
    {
        return $this->doctrine
            ->getManager();
    }
}
