<?php

namespace Marello\Bundle\InventoryBundle\Logging;

use Closure;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;

class InventoryLogger
{
    const MANUAL_TRIGGER = 'manual';

    /** @var Registry */
    protected $doctrine;

    /** @var TokenStorageInterface */
    protected $storage;

    /**
     * InventoryLogger constructor.
     *
     * @param Registry              $doctrine
     * @param TokenStorageInterface $storage
     */
    public function __construct(Registry $doctrine, TokenStorageInterface $storage = null)
    {
        $this->doctrine = $doctrine;
        $this->storage  = $storage;
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

        if ($trigger === self::MANUAL_TRIGGER) {
            $this->setUserReference($log);
        }

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

            if ($trigger === self::MANUAL_TRIGGER) {
                $this->setUserReference($log);
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
        if (!$inventoryItem->getQuantity() && !$inventoryItem->getAllocatedQuantity()) {
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

        /*
         * Check if any of quantities were changed.
         */
        $quantityChanged          = $this->quantityChanged('quantity', $changeSet);
        $allocatedQuantityChanged = $this->quantityChanged('allocatedQuantity', $changeSet);

        /*
         * If no quantity was changed, return null and do not log any changes.
         */
        if (!$quantityChanged && !$allocatedQuantityChanged) {
            return null;
        }

        $log = new InventoryLog($item, $trigger);

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
     * Check if quantity for the field has really changed based
     * on the values entered
     *
     * @param $field
     * @param $changeSet
     *
     * @return bool
     */
    protected function quantityChanged($field, $changeSet)
    {
        if (!array_key_exists($field, $changeSet)) {
            return false;
        }

        return ((int)$changeSet[$field][0] !== (int)$changeSet[$field][1]);
    }

    /**
     * Set user as reference on inventory log item
     *
     * @param InventoryLog $logItem
     */
    protected function setUserReference(InventoryLog $logItem)
    {
        if (null === $this->storage) {
            return;
        }

        if (null !== $this->storage->getToken()) {
            $user = $this->storage->getToken()->getUser();
            $logItem->setUser($user);
        }
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
