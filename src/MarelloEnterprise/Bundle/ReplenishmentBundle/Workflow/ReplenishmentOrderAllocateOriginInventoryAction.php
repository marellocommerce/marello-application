<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\ConfigExpression\ContextAccessor;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Provider\ReplenishmentOrdersFromConfigProvider;

class ReplenishmentOrderAllocateOriginInventoryAction extends ReplenishmentOrderTransitionAction
{
    public function __construct(
        ContextAccessor $contextAccessor,
        EventDispatcherInterface $eventDispatcher,
        protected ReplenishmentOrdersFromConfigProvider $replenishmentOrdersProvider,
        protected ManagerRegistry $registry
    ) {
        parent::__construct($contextAccessor, $eventDispatcher);
    }

    /**
     * @param WorkflowItem|mixed $context
     */
    protected function executeAction($context)
    {
        /** @var ReplenishmentOrder $order */
        $order = $context->getEntity();
        
        $calculatedOrders = $this->replenishmentOrdersProvider
            ->getReplenishmentOrders(
                $order->getReplOrderConfig(),
                true
            );
        foreach ($calculatedOrders as $calculatedOrder) {
            if ($order->getOrigin()->getCode() !== $calculatedOrder->getOrigin()->getCode() ||
                $order->getDestination()->getCode() !== $calculatedOrder->getDestination()->getCode()
            ) {
                continue;
            }

            /** @var ReplenishmentOrderItem $orderItem */
            foreach ($order->getReplOrderItems()->toArray() as $orderItem) {
                /** @var ReplenishmentOrderItem $calcOrderItem */
                foreach ($calculatedOrder->getReplOrderItems()->toArray() as $calcOrderItem) {
                    if ($orderItem->getProductSku() !== $calcOrderItem->getProductSku()) {
                        continue;
                    }

                    [$inventoryQty, $totalInventoryQty] = $this->getQuantities($calcOrderItem);
                    $orderItem
                        ->setInventoryQty($inventoryQty)
                        ->setTotalInventoryQty($totalInventoryQty)
                        ->setAllQuantity($calcOrderItem->isAllQuantity())
                        ->setInventoryBatches($calcOrderItem->getInventoryBatches());
                    $em = $this->registry->getManagerForClass(ReplenishmentOrderItem::class);
                    $em->persist($orderItem);
                    $em->flush($orderItem);
                }
            }
        }

        $warehouse = $order->getOrigin();
        $items = $order->getReplOrderItems();
            $items->map(function (ReplenishmentOrderItem $item) use ($order, $warehouse) {
                $this->handleInventoryUpdate(
                    $item,
                    null,
                    $item->getInventoryQty(),
                    $warehouse,
                    $order
                );
            });
    }

    protected function getQuantities(ReplenishmentOrderItem $orderItem): array
    {
        if ($orderItem->isAllQuantity()) {
            $inventoryItem = $orderItem->getProduct()->getInventoryItem();
            $qty = 0;
            foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                if ($inventoryLevel->getWarehouse() === $orderItem->getOrder()->getOrigin()) {
                    $qty = $inventoryLevel->getInventoryQty();
                    break;
                }
            }
            $inventoryQty = $totalInventoryQty = $qty;
        } else {
            $inventoryQty = $orderItem->getInventoryQty();
            $totalInventoryQty = $orderItem->getTotalInventoryQty();
        }

        return [$inventoryQty, $totalInventoryQty];
    }

    /**
     * handle the inventory update for items which have been shipped
     * @param ReplenishmentOrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param Warehouse $warehouse
     * @param ReplenishmentOrder $order
     */
    protected function handleInventoryUpdate(
        $item,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $warehouse,
        $order
    ) {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'marelloenterprise.replenishment.replenishmentorder.workflow.prepared_for_shipping',
            $order
        );

        $context->setValue('warehouse', $warehouse);

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }
}
