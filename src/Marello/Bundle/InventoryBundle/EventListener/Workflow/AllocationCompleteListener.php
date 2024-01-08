<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Workflow;

use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Marello\Bundle\OrderBundle\Model\WorkflowNameProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\Action\Event\ExtendableActionEvent;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\PackingBundle\Entity\PackingSlipItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryBatch;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\OrderBundle\Model\OrderItemStatusesInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryTotalCalculator;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\InventoryBundle\Provider\AllocationContextInterface;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Marello\Bundle\WorkflowBundle\Async\Topic\WorkflowTransitTopic;

class AllocationCompleteListener
{
    use JobIdGenerationTrait;

    const TRANSIT_TO_STEP = 'ship';

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /** @var MessageProducerInterface $messageProducer */
    protected $messageProducer;

    /** @var InventoryTotalCalculator $totalCalculator */
    protected $totalCalculator;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /**
     * AllocationCompleteListener constructor.
     * @param DoctrineHelper $doctrineHelper
     * @param WorkflowManager $workflowManager
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        WorkflowManager $workflowManager,
        MessageProducerInterface $messageProducer,
        InventoryTotalCalculator $totalCalculator
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->workflowManager = $workflowManager;
        $this->messageProducer = $messageProducer;
        $this->totalCalculator = $totalCalculator;
    }

    /**
     * @param ExtendableActionEvent $event
     */
    public function onAllocationComplete(ExtendableActionEvent $event)
    {
        if (!$this->isCorrectContext($event->getContext())) {
            return;
        }
        /** @var Allocation $entity */
        $entity = $event->getContext()->getData()->get('allocation');
        /** @var Order $order */
        $order = $entity->getOrder();
        $shippedItems = [];
        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            // all items which have complete or shipped status
            $orderStatuses = [
                OrderItemStatusesInterface::OIS_PROCESSING,
                OrderItemStatusesInterface::OIS_SHIPPED,
                OrderItemStatusesInterface::OIS_COMPLETE,
            ];
            if ($item->getStatus() && in_array($item->getStatus()->getId(), $orderStatuses)) {
                // use the InventoryTotalCalculator::getTotalAllocationQtyConfirmed for a more coherent calculation of
                // allocation item totals, this is meant for this specific purpose
                // don't reinvent the wheel.
                // all allocation items
                $allocationItems = $this->doctrineHelper
                    ->getEntityRepositoryForClass(AllocationItem::class)
                    ->findBy(['orderItem' => $item->getId()]);
                // total qty of the item for complete items is the quantity of the order item
                $orderItemQty = (empty($allocationItems)) ? $item->getQuantity() : 0;
                /** @var AllocationItem $allocationItem */
                foreach ($allocationItems as $allocationItem) {
                    // if we add the all allocation confirmed quantities including the parent we would always
                    // have allocated more quantity than we've ordered..
                    if (!$allocationItem->getAllocation()->getParent()) {
                        $orderItemQty += $allocationItem->getQuantityConfirmed();
                    }
                }

                if ((int)$orderItemQty === $item->getQuantity() ||
                    ($entity->getAllocationContext() &&
                    $entity->getAllocationContext()->getId() ===
                    AllocationContextInterface::ALLOCATION_CONTEXT_RESHIPMENT)
                ) {
                    $shippedItems[] = $item->getId();
                }
            }
        }

        // shipped items take quantities in account from above
        // parent needs to be empty, as this might indicate that this order should be consolidated
        if (count($shippedItems) === $order->getItems()->count() && !$entity->getParent()) {
            if ($entity->getAllocationContext() &&
                $entity->getAllocationContext()->getId() === AllocationContextInterface::ALLOCATION_CONTEXT_RESHIPMENT
            ) {
                /** @var Allocation $entity */
                if ($entity->getState()
                    && $entity->getState()->getId() !== AllocationStateStatusInterface::ALLOCATION_STATE_AVAILABLE
                ) {
                    return;
                }

                $warehouse = $entity->getWarehouse();
                $items = $entity->getItems();
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

            // order is considered complete
            if ($this->getApplicableWorkflow($order)) {
                /** @var WorkflowItem $workflowItem */
                $workflowItem = $this
                    ->doctrineHelper
                    ->getEntityRepositoryForClass(WorkflowItem::class)
                    ->findOneBy(
                        [
                            'entityId' => $order->getId(),
                            'entityClass' => Order::class
                        ]
                    );
                $this->messageProducer->send(
                    WorkflowTransitTopic::getName(),
                    [
                        'workflow_item_entity_id' => $order->getId(),
                        'current_step_id' => $workflowItem->getCurrentStep()->getId(),
                        'entity_class' => Order::class,
                        'transition' => self::TRANSIT_TO_STEP,
                        'priority' => MessagePriority::NORMAL
                    ]
                );
            }
        }
    }

    /**
     * @param $entity
     * @return Workflow|null
     */
    protected function getApplicableWorkflow($entity): ?Workflow
    {
        if (!$this->workflowManager->hasApplicableWorkflows($entity)) {
            return null;
        }

        $applicableWorkflows = [];
        // apply force autostart (ignore default filters)
        $workflows = $this->workflowManager->getApplicableWorkflows($entity);
        foreach ($workflows as $name => $workflow) {
            if (in_array($name, $this->getDefaultWorkflowNames())) {
                $applicableWorkflows[$name] = $workflow;
            }
        }

        if (count($applicableWorkflows) !== 1) {
            return null;
        }

        return array_shift($applicableWorkflows);
    }

    /**
     * @param mixed $context
     * @return bool
     */
    protected function isCorrectContext($context)
    {
        return ($context instanceof WorkflowItem
            && $context->getData() instanceof WorkflowData
            && $context->getData()->has('allocation')
            && $context->getData()->get('allocation') instanceof Allocation
        );
    }

    /**
     * @return array
     */
    protected function getDefaultWorkflowNames(): array
    {
        return [
            WorkflowNameProviderInterface::ORDER_WORKFLOW_1,
            WorkflowNameProviderInterface::ORDER_WORKFLOW_2,
            WorkflowNameProviderInterface::ORDER_POS_WORKFLOW
        ];
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
        $packingSlipItem = $this->doctrineHelper
            ->getEntityManagerForClass(PackingSlipItem::class)
            ->getRepository(PackingSlipItem::class)
            ->findOneBy(['orderItem' => $item]);
        if ($packingSlipItem) {
            if (!empty($packingSlipItem->getInventoryBatches())) {
                $contextBranches = [];
                foreach ($packingSlipItem->getInventoryBatches() as $batchNumber => $qty) {
                    /** @var InventoryBatch[] $inventoryBatches */
                    $inventoryBatches = $this->doctrineHelper
                        ->getEntityManagerForClass(InventoryBatch::class)
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

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
