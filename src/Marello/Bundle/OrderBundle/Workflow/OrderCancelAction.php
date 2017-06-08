<?php

namespace Marello\Bundle\OrderBundle\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\ConfigExpression\ContextAccessor;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class OrderCancelAction extends OrderTransitionAction
{
    /** @var Registry */
    protected $doctrine;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /**
     * OrderShipAction constructor.
     *
     * @param ContextAccessor           $contextAccessor
     * @param Registry                  $doctrine
     * @param EventDispatcherInterface  $eventDispatcher
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
            $this->handleInventoryUpdate($item, null, -$item->getQuantity(), $order);
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
//        $inventoryItems = $item->getProduct()->getInventoryItems();
//        $inventoryItemData = [];
//        foreach ($inventoryItems as $inventoryItem) {
//            $inventoryItemData[] = [
//                'item'          => $inventoryItem,
//                'qty'           => $inventoryUpdateQty,
//                'allocatedQty'  => $allocatedInventoryQty
//            ];
//        }
//
//        $data = [
//            'stock'             => $inventoryUpdateQty,
//            'allocatedStock'    => $allocatedInventoryQty,
//            'trigger'           => 'order_workflow.cancelled',
//            'items'             => $inventoryItemData,
//            'relatedEntity'     => $entity
//        ];
//
//        $context = InventoryUpdateContext::createUpdateContext($data);

        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'order_workflow.cancelled',
            $entity
        );

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );
    }
}
