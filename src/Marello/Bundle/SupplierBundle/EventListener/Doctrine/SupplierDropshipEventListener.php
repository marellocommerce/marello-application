<?php

namespace Marello\Bundle\SupplierBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\SupplierBundle\Event\SupplierDropshipEvent;
use Oro\Bundle\EntityBundle\Event\OroEventManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SupplierDropshipEventListener
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Supplier && $entity->getCanDropship() === true) {
            $this->eventDispatcher->dispatch(
                new SupplierDropshipEvent($entity, true),
                SupplierDropshipEvent::NAME
            );
        }
    }
    
    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Supplier && $args->hasChangedField('canDropship')) {
            $em = $args->getObjectManager();
            /** @var OroEventManager $eventManager */
            $eventManager = $em->getEventManager();
            $eventManager->removeEventListener('preUpdate', $this);
            if ($entity->getCanDropship() === true) {
                $this->eventDispatcher->dispatch(
                    new SupplierDropshipEvent($entity, true),
                    SupplierDropshipEvent::NAME
                );
            } else {
                $this->eventDispatcher->dispatch(
                    new SupplierDropshipEvent($entity, false),
                    SupplierDropshipEvent::NAME
                );
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Supplier && $entity->getCanDropship() === true) {
            $this->eventDispatcher->dispatch(
                new SupplierDropshipEvent($entity, false),
                SupplierDropshipEvent::NAME
            );
        }
    }
}
