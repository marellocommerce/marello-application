<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReplenishmentOrderAllocateDestinationInventoryAction extends ReplenishmentOrderTransitionAction
{
    public function __construct(
        ContextAccessor $contextAccessor,
        EventDispatcherInterface $eventDispatcher,
        protected ManagerRegistry $doctrine
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
        $originWarehouse = $order->getOrigin();
        $destinationWarehouse = $order->getDestination();
        $items = $order->getReplOrderItems();
        $items->map(function (ReplenishmentOrderItem $item) use ($order, $originWarehouse, $destinationWarehouse) {
            $this->handleInventoryUpdate(
                $item,
                $item->getInventoryQty(),
                0,
                $originWarehouse,
                $destinationWarehouse,
                $order
            );
        });
    }

    /**
     * handle the inventory update for items which have been shipped
     * @param ReplenishmentOrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param Warehouse $originWarehouse
     * @param Warehouse $destinationWarehouse
     * @param ReplenishmentOrder $order
     */
    protected function handleInventoryUpdate(
        $item,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $originWarehouse,
        $destinationWarehouse,
        $order
    ) {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'marelloenterprise.replenishment.replenishmentorder.workflow.completed',
            $order
        );
        if (!empty($item->getInventoryBatches())) {
            $contextBranches = [];
            foreach ($item->getInventoryBatches() as $batchNumber => $qty) {
                /** @var InventoryBatch[] $inventoryBatches */
                $inventoryBatches = $this->doctrine
                    ->getManagerForClass(InventoryBatch::class)
                    ->getRepository(InventoryBatch::class)
                    ->findBy(['batchNumber' => $batchNumber]);
                $originInventoryBatch = null;
                $destinationInventoryBatch = null;
                foreach ($inventoryBatches as $batch) {
                    $inventoryLevel = $batch->getInventoryLevel();
                    if ($inventoryLevel && $inventoryLevel->getWarehouse() === $originWarehouse) {
                        $originInventoryBatch = $batch;
                    }
                    if ($inventoryLevel && $inventoryLevel->getWarehouse() === $destinationWarehouse) {
                        $destinationInventoryBatch = $batch;
                    }
                }
                if ($originInventoryBatch && !$destinationInventoryBatch) {
                    $inventoryItem = $item->getProduct()->getInventoryItem();
                    if ($inventoryItem) {
                        $destinationInventoryBatch = clone $originInventoryBatch;
                        $destinationInventoryBatch->setQuantity(0);
                        $destinationInventoryLevel = $inventoryItem->getInventoryLevel($destinationWarehouse);
                        if ($destinationInventoryLevel) {
                            $destinationInventoryBatch->setInventoryLevel($destinationInventoryLevel);
                        }
                    }
                }
                if ($destinationInventoryBatch) {
                    $contextBranches[] = ['batch' => $destinationInventoryBatch, 'qty' => $qty];
                }
            }
            $context->setInventoryBatches($contextBranches);
        }
        $context->setValue('warehouse', $destinationWarehouse);

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }
}
