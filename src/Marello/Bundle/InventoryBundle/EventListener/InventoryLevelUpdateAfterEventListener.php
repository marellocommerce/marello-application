<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevelLogRecord;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class InventoryLevelUpdateAfterEventListener
{
    /** @var DoctrineHelper $doctrineHelper*/
    protected $doctrineHelper;

    /**
     * InventoryLevelUpdateAfterEventListener constructor.
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Handle incoming event
     * @param InventoryUpdateEvent $event
     * @return mixed
     */
    public function handleInventoryLevelUpdateAfterEvent(InventoryUpdateEvent $event)
    {
        /** @var InventoryUpdateContext $context */
        $context = $event->getInventoryUpdateContext();
        if ($context->getIsVirtual()) {
            // do nothing when context is for virtual inventory levels
            return;
        }

        $this->createLogRecord(
            $context->getInventoryLevel(),
            $context->getInventory(),
            $context->getAllocatedInventory(),
            $context->getChangeTrigger(),
            $context->getUser(),
            $context->getRelatedEntity()
        );
    }


    /**
     * @param InventoryLevel    $level                  InventoryLevel to be updated
     * @param int|null          $inventoryAlt           Inventory Change qty, qty that represents the actual change
     * @param int|null          $allocatedInventoryAlt  Allocated Inventory Change qty, qty that represents the
     *                                                  actual change
     * @param string            $trigger                Action that triggered the change
     * @param User|null         $user                   User who triggered the change, if left null,
     *                                                  it is automatically assigned to current one
     * @param mixed|null        $subject                Any entity that should be associated to this operation
     */
    private function createLogRecord(
        InventoryLevel $level,
        $inventoryAlt,
        $allocatedInventoryAlt,
        $trigger,
        $user,
        $subject
    ) {
        if ($inventoryAlt === null) {
            $inventoryAlt = 0;
        }

        if ($allocatedInventoryAlt === null) {
            $allocatedInventoryAlt = 0;
        }

        // no inventory level has changed, so logging it is pointless...
        if ($inventoryAlt === 0 && $allocatedInventoryAlt === 0) {
            return;
        }

        $record = new InventoryLevelLogRecord(
            $level,
            $inventoryAlt,
            $allocatedInventoryAlt,
            $trigger,
            $user,
            $subject
        );

        $em = $this->doctrineHelper->getEntityManagerForClass(InventoryLevelLogRecord::class);
        $em->persist($level);
        $em->persist($record);
    }
}
