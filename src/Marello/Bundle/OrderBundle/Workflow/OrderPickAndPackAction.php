<?php

namespace Marello\Bundle\OrderBundle\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\ConfigExpression\ContextAccessor;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;

class OrderPickAndPackAction extends OrderTransitionAction
{
    /** @var Registry */
    protected $doctrine;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /** @var OrderWarehousesProviderInterface $warehousesProvider */
    protected $warehousesProvider;

    /**
     * OrderPickAndPackAction constructor.
     *
     * @param ContextAccessor           $contextAccessor
     * @param Registry                  $doctrine
     * @param EventDispatcherInterface  $eventDispatcher
     * @param OrderWarehousesProviderInterface $warehousesProvider
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        Registry $doctrine,
        EventDispatcherInterface $eventDispatcher,
        OrderWarehousesProviderInterface $warehousesProvider
    ) {
        parent::__construct($contextAccessor);

        $this->doctrine = $doctrine;
        $this->eventDispatcher = $eventDispatcher;
        $this->warehousesProvider = $warehousesProvider;
    }

    /**
     * @param WorkflowItem|mixed $context
     */
    protected function executeAction($context)
    {
        /** @var Order $order */
        $order = $context->getEntity();
        foreach ($this->warehousesProvider->getWarehousesForOrder($order) as $result) {
            $warehouse = $result->getWarehouse();
            $items = $result->getOrderItems();
            $items->map(function (OrderItem $item) use ($order, $warehouse) {
                $this->handleInventoryUpdate($item, null, $item->getQuantity(), $order, $warehouse);
            });
        }
    }

    /**
     * handle the inventory update for items which have been picked and packed
     * @param OrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param Order $entity
     * @param Warehouse $warehouse
     */
    protected function handleInventoryUpdate(
        $item,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $entity,
        $warehouse
    ) {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'order_workflow.inventory_allocated',
            $entity
        );

        $context->setValue('warehouse', $warehouse);

        $this->eventDispatcher->dispatch(
            InventoryUpdateEvent::NAME,
            new InventoryUpdateEvent($context)
        );
    }
}
