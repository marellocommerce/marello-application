<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        $entity = $args->getObject();
        if (!$entity instanceof Order) {
            return;
        }

        $entity->getItems()->map(function (OrderItem $item) use ($entity) {
            if (!$item->isAllocationExclusion()) {
                $this->handleInventoryUpdate($item, $item->getQuantity(), $entity);
            }
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
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }
}
