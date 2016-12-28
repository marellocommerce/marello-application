<?php

namespace Marello\Bundle\OrderBundle\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\Action\Model\ContextAccessor;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class OrderShipAction extends OrderTransitionAction
{
    /** @var Registry */
    protected $doctrine;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /**
     * OrderShipAction constructor.
     *
     * @param ContextAccessor $contextAccessor
     * @param Registry        $doctrine
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        Registry $doctrine,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($contextAccessor);

        $this->doctrine = $doctrine;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param WorkflowItem|mixed $context
     */
    protected function executeAction($context)
    {
        /** @var Order $order */
        $order = $context->getEntity();

        $order->getItems()->map(function (OrderItem $item) use ($order) {
            $this->handleInventoryUpdate($item, -$item->getQuantity(), -$item->getQuantity(), $order);
        });
    }

    /**
     * handle the inventory update for items which have been shipped
     * @param OrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param OrderItem $entity
     */
    protected function handleInventoryUpdate($item, $inventoryUpdateQty, $allocatedInventoryQty, $entity)
    {
        $inventoryItems = $item->getProduct()->getInventoryItems();
        $inventoryItemData = [];
        foreach ($inventoryItems as $inventoryItem) {
            $inventoryItemData[] = [
                'item'          => $inventoryItem,
                'qty'           => $inventoryUpdateQty,
                'allocatedQty'  => $allocatedInventoryQty
            ];
        }

        $data = [
            'stock'             => $inventoryUpdateQty,
            'allocatedStock'    => $allocatedInventoryQty,
            'trigger'           => 'order_workflow.shipped',
            'items'             => $inventoryItemData,
            'relatedEntity'     => $entity
        ];

        $context = InventoryUpdateContext::createUpdateContext($data);
        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );
    }
}
