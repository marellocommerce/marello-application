<?php

namespace Marello\Bundle\ProductBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\ProductBundle\Event\ProductDropshipEvent;
use Oro\Bundle\EntityBundle\Event\OroEventManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductDropshipEventListener
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ProductDropshipEvent
     */
    protected $event;

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
        if ($entity instanceof ProductSupplierRelation && $entity->getCanDropship() === true) {
            $this->event= new ProductDropshipEvent($entity, true);
        }
    }
    
    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ProductSupplierRelation && $args->hasChangedField('canDropship')) {
            $em = $args->getEntityManager();
            /** @var OroEventManager $eventManager */
            $eventManager = $em->getEventManager();
            $eventManager->removeEventListener('preUpdate', 'marello_product.listener.doctrine.product_dropship');
            if ($entity->getCanDropship() === true) {
                $this->event = new ProductDropshipEvent($entity, true);
            } else {
                $this->event = new ProductDropshipEvent($entity, false);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ProductSupplierRelation && $entity->getCanDropship() === true) {
            $this->event= new ProductDropshipEvent($entity, false);
        }
    }

    public function postFlush()
    {
        if ($this->event !== null) {
            $event = $this->event;
            $this->event = null;
            $this->eventDispatcher->dispatch(
                $event,
                ProductDropshipEvent::NAME
            );
        }
    }
}
