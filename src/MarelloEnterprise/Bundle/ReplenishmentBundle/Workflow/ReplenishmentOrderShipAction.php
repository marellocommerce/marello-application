<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReplenishmentOrderShipAction extends ReplenishmentOrderTransitionAction
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @param ContextAccessor           $contextAccessor
     * @param EventDispatcherInterface  $eventDispatcher
     * @param Registry                  $doctrine
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        EventDispatcherInterface $eventDispatcher,
        Registry $doctrine
    ) {
        parent::__construct($contextAccessor, $eventDispatcher);

        $this->doctrine = $doctrine;
    }

    /**
     * @param WorkflowItem|mixed $context
     */
    protected function executeAction($context)
    {
        /** @var ReplenishmentOrder $order */
        $order = $context->getEntity();
        $warehouse = $order->getOrigin();
        $items = $order->getReplOrderItems();
        $items->map(function (ReplenishmentOrderItem $item) use ($order, $warehouse) {
            $this->handleInventoryUpdate(
                $item,
                -$item->getInventoryQty(),
                -$item->getInventoryQty(),
                $warehouse,
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
            'marelloenterprise.replenishment.replenishmentorder.workflow.shipped',
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
                $inventoryBatch = null;
                foreach ($inventoryBatches as $batch) {
                    $inventoryLevel = $batch->getInventoryLevel();
                    if ($inventoryLevel && $inventoryLevel->getWarehouse() === $warehouse) {
                        $inventoryBatch = $batch;
                    }
                }
                if ($inventoryBatch) {
                    $contextBranches[] = ['batch' => $inventoryBatch, 'qty' => -$qty];
                }
            }
            $context->setInventoryBatches($contextBranches);
        }
        $context->setValue('warehouse', $warehouse);

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }
}
