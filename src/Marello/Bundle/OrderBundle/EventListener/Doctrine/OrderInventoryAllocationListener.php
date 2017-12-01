<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class OrderInventoryAllocationListener
{
    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Order) {
            return;
        }

        $entity->getItems()->map(function (OrderItem $item) use ($entity, $args) {
            $this->handleInventoryUpdate($item, $item->getQuantity(), $entity);
        });
    }

    /**
     * handle the inventory update for items which have been received
     * @param OrderItem $item
     * @param $inventoryUpdateQty
     * @param Order $order
     */
    protected function handleInventoryUpdate($item, $inventoryUpdateQty, $order)
    {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            null,
            $inventoryUpdateQty,
            'order_workflow.pending',
            $order,
            true
        );

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );
    }
}
