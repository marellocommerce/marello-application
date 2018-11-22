<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReplenishmentOrderItemListener
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface  $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    
    /**
     * @param ReplenishmentOrderItem $item
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(ReplenishmentOrderItem $item, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $changes = $em->getUnitOfWork()->getEntityChangeSet($item);
        if (isset($changes['inventoryQty']) && $changes['inventoryQty'][0] !== $changes['inventoryQty'][1]) {
            $diff = $changes['inventoryQty'][1] - $changes['inventoryQty'][0];
            $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
                $item,
                null,
                -$diff,
                $diff,
                'replenishment_order_workflow.check_and_pack'
            );

            $context->setValue('warehouse', $item->getOrder()->getOrigin());

            $this->eventDispatcher->dispatch(
                InventoryUpdateEvent::NAME,
                new InventoryUpdateEvent($context)
            );
        }
        
    }
}