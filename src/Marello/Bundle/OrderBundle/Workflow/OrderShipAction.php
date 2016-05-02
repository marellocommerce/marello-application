<?php

namespace Marello\Bundle\OrderBundle\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class OrderShipAction extends OrderTransitionAction
{
    /** @var Registry */
    protected $doctrine;

    /** @var InventoryItem[] */
    protected $changedInventory;

    /**
     * OrderShipAction constructor.
     *
     * @param ContextAccessor $contextAccessor
     * @param Registry        $doctrine
     */
    public function __construct(ContextAccessor $contextAccessor, Registry $doctrine)
    {
        parent::__construct($contextAccessor);

        $this->doctrine = $doctrine;
    }

    /**
     * @param WorkflowItem|mixed $context
     */
    protected function executeAction($context)
    {
        /** @var Order $order */
        $order = $context->getEntity();

        $this->changedInventory = [];

        $order->getItems()->map(function (OrderItem $orderItem) {
            $this->shipOrderItem($orderItem);
        });

        $this->doctrine->getManager()->flush();
    }

    /**
     * Deallocates all items allocated to this item and reduces real stock, indicating that item has been shipped.
     *
     * @param OrderItem $orderItem
     */
    protected function shipOrderItem(OrderItem $orderItem)
    {
        $allocations = $orderItem->getInventoryAllocations();

        foreach ($allocations as $allocation) {
            $this->changedInventory[] = $inventoryItem = $allocation->getInventoryItem();

            /*
             * Reduce inventory item real stock by allocated amount.
             */
            $inventoryItem->modifyQuantity(-$allocation->getQuantity());

            /*
             * When allocation is removed, the allocated amount on inventory amount will be automatically decreased.
             */
            $this->doctrine->getManager()->remove($allocation);
            $this->doctrine->getManager()->persist($inventoryItem);
        }
    }
}
