<?php

namespace Marello\Bundle\OrderBundle\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadAllocationStatusData;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderShipAction extends OrderTransitionAction
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
        $allocations = $this
            ->doctrine
            ->getRepository(Allocation::class)
            ->findBy(['order' => $order]);
        /** @var Allocation $result */
        foreach ($allocations as $result) {
            if ($result->getState()
                && $result->getState()->getId() !== AllocationStateStatusInterface::ALLOCATION_STATE_AVAILABLE
            ) {
                continue;
            }

            $warehouse = $result->getWarehouse();
            $items = $result->getItems();
            $items->map(function (AllocationItem $item) use ($order, $warehouse) {
                $this->handleInventoryUpdate(
                    $item->getOrderItem(),
                    -$item->getQuantity(),
                    -$item->getQuantity(),
                    $order,
                    $warehouse
                );
            });
        }
    }

    /**
     * handle the inventory update for items which have been shipped
     * @param OrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param Order $entity
     * @param Warehouse $warehouse
     */
    protected function handleInventoryUpdate($item, $inventoryUpdateQty, $allocatedInventoryQty, $entity, $warehouse)
    {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'order_workflow.shipped',
            $entity
        );
        $packingSlipItem = $this->doctrine
            ->getManagerForClass(PackingSlipItem::class)
            ->getRepository(PackingSlipItem::class)
            ->findOneBy(['orderItem' => $item]);
        if ($packingSlipItem) {
            if (!empty($packingSlipItem->getInventoryBatches())) {
                $contextBranches = [];
                foreach ($packingSlipItem->getInventoryBatches() as $batchNumber => $qty) {
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
        }
        $context->setValue('warehouse', $warehouse);

        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );
    }
}
