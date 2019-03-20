<?php

namespace Marello\Bundle\SupplierBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
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
        $entity = $args->getEntity();
        if ($entity instanceof Supplier && $entity->getCanDropship() === true) {
            $this->eventDispatcher->dispatch(
                SupplierDropshipEvent::NAME,
                new SupplierDropshipEvent($entity, true)
            );
        }
    }
    
    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Supplier && $args->hasChangedField('canDropship')) {
            $em = $args->getEntityManager();
            /** @var OroEventManager $eventManager */
            $eventManager = $em->getEventManager();
            $eventManager->removeEventListener('preUpdate', 'marello_supplier.event_listener.dropship');
            if ($entity->getCanDropship() === true) {
                $this->eventDispatcher->dispatch(
                    SupplierDropshipEvent::NAME,
                    new SupplierDropshipEvent($entity, true)
                );
            } else {
                $this->eventDispatcher->dispatch(
                    SupplierDropshipEvent::NAME,
                    new SupplierDropshipEvent($entity, false)
                );
            }
        }
    }
}
