<?php

namespace Marello\Bundle\OrderBundle\Workflow;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\ConfigExpression\ContextAccessor;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class OrderCancelAction extends OrderTransitionAction
{
    /** @var boolean */
    protected $shouldUpdateBalancedInventory;

    public function __construct(
        ContextAccessor $contextAccessor,
        protected ManagerRegistry $doctrine
    ) {
        parent::__construct($contextAccessor);
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function initialize(array $options)
    {
        parent::initialize($options);

        $this->shouldUpdateBalancedInventory = $this->getOption($options, 'update_balanced_inventory', true);

        return $this;
    }

    /**
     * @param WorkflowItem|mixed $context
     */
    protected function executeAction($context)
    {
        /** @var Order $order */
        $order = $context->getEntity();

        $this->shouldUpdateBalancedInventory = $this->contextAccessor->getValue(
            $context,
            $this->shouldUpdateBalancedInventory
        );

        $order->getItems()->map(function (OrderItem $item) use ($order) {
            $this->handleInventoryUpdate($item, null, -$item->getQuantity(), $order);
        });
    }

    /**
     * handle the inventory update for the order's items which have cancelled
     * @param OrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param Order $entity
     */
    protected function handleInventoryUpdate($item, $inventoryUpdateQty, $allocatedInventoryQty, $entity)
    {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'order_workflow.cancelled',
            $entity,
            $this->shouldUpdateBalancedInventory
        );

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }
}
