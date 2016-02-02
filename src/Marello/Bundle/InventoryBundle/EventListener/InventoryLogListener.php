<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
use Marello\Bundle\InventoryBundle\Events\InventoryLogEvent;

class InventoryLogListener
{
    /** @var array */
    protected $supportedTypes = ['manual'];

    /** @var Registry */
    protected $doctrine;

    /**
     * InventoryLogListener constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param InventoryLogEvent $event
     */
    public function log(InventoryLogEvent $event)
    {
        /*
         * If event is of unsupported type, don't handle it.
         */
        if (!in_array($event->getType(), $this->supportedTypes)) {
            return;
        }

        $log = new InventoryLog();

        $log
            ->setInventoryItem($event->getInventoryItem())
            ->setActionType($event->getType())
            ->setOldQuantity($event->getOldQuantity())
            ->setNewQuantity($event->getNewQuantity())
            ->setUser($event->getUser());

        /*
         * Only persist new log, no need to flush .. that would be done after product is persisted.
         */
        $this->doctrine->getManager()->persist($log);
    }
}
