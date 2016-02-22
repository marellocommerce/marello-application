<?php

namespace Marello\Bundle\InventoryBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryLog;
use Marello\Bundle\InventoryBundle\Events\InventoryLogEvent;

class InventoryLogListener
{
    /**
     * @var array Supported inventory log event types.
     */
    protected $supportedTypes = [
        'manual',       // Event caused by manual change of quantity (by user trough UI).
        'import',       // Event caused by importing inventory changes.
        'workflow'      // Event caused by change in workflow status of an Order for example.
    ];

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
     * Process given inventory log event.
     *
     * Responsible for creating inventory log record in database.
     *
     * @param InventoryLogEvent $event
     */
    public function log(InventoryLogEvent $event)
    {
        /*
         * If event is of unsupported type, don't handle it.
         */
        if (!in_array($event->getTrigger(), $this->supportedTypes)) {
            return;
        }

        $log = new InventoryLog();

        $log
            ->setInventoryItem($event->getInventoryItem())
            ->setActionType($event->getTrigger())
            ->setInventoryType($event->getInventoryType())
            ->setOldQuantity($event->getOldQuantity())
            ->setNewQuantity($event->getNewQuantity())
            ->setUser($event->getUser());

        /*
         * New log is only persisted, not flushed. This is done in order to not mess with any transaction login in
         * progress. If event is fired outside of such logic, flush the EM manually.
         */
        $this->doctrine->getManager()->persist($log);
    }
}
