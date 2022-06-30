<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class ReplenishmentOrderCancelAction extends ReplenishmentOrderTransitionAction
{
    /**
     * @param WorkflowItem|mixed $context
     */
    protected function executeAction($context)
    {
        /** @var ReplenishmentOrder $order */
        $order = $context->getEntity();

        $order->getReplOrderItems()->map(function (ReplenishmentOrderItem $item) use ($order) {
            $this->handleInventoryUpdate(
                $item,
                null,
                -$item->getInventoryQty(),
                $order->getOrigin(),
                $order
            );
        });
    }

    /**
     * handle the inventory update for items which have been shipped
     * @param ReplenishmentOrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param Warehouse $warehouse
     * @param ReplenishmentOrder $order
     */
    protected function handleInventoryUpdate($item, $inventoryUpdateQty, $allocatedInventoryQty, $warehouse, $order)
    {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'marelloenterprise.replenishment.replenishmentorder.workflow.cancelled',
            $order
        );

        $context->setValue('warehouse', $warehouse);

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }
}
