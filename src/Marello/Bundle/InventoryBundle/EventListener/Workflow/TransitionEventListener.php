<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Workflow;

use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Marello\Bundle\WorkflowBundle\Async\Topic\WorkflowTransitTopic;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\Action\Event\ExtendableActionEvent;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceInventoryTopic;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class TransitionEventListener
{
    use JobIdGenerationTrait;

    const WORKFLOW_STEP_FROM = 'pending';
    const WORKFLOW_NAME = 'marello_allocate_workflow';
    const CONTEXT_KEY = 'allocation';
    const ALLOCATION_WORKFLOW_RESOLVED = 'resolved';

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /** @var MessageProducerInterface $messageProducer */
    protected $messageProducer;

    /** @var EventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;

    /**
     * TransitionEventListener constructor.
     * @param WorkflowManager $workflowManager
     * @param MessageProducerInterface $messageProducer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        WorkflowManager $workflowManager,
        MessageProducerInterface $messageProducer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->workflowManager = $workflowManager;
        $this->messageProducer = $messageProducer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ExtendableActionEvent $event
     * @throws \Exception
     */
    public function onPendingTransitionAfter(ExtendableActionEvent $event)
    {
        if (!$this->isCorrectContext($event->getContext())) {
            return;
        }

        /** @var Allocation $entity */
        $entity = $event->getContext()->getData()->get(self::CONTEXT_KEY);

        if ($this->getApplicableWorkflow($entity)) {
            if ($entity->getStatus()->getName() === AllocationStateStatusInterface::ALLOCATION_STATUS_CNA) {
                if ($event->getContext()->getCurrentStep()->getName() === self::WORKFLOW_STEP_FROM) {
                    $this->messageProducer->send(
                        WorkflowTransitTopic::getName(),
                        [
                            'workflow_item_entity_id' => $entity->getId(),
                            'current_step_id' => $event->getContext()->getCurrentStep()->getId(),
                            'entity_class' => Allocation::class,
                            'transition' => self::ALLOCATION_WORKFLOW_RESOLVED,
                            'priority' => MessagePriority::NORMAL
                        ]
                    );
                }
            }

            if ($entity->getStatus()->getName() !== AllocationStateStatusInterface::ALLOCATION_STATUS_CNA) {
                if ($entity->getWarehouse() && !$entity->hasChildren()) {
                    // allocations that can be allocated and are not the parent allocation for the consolidation
                    // option, we should decrease the reserved inventory quantity
                    $entity->getItems()->map(function (AllocationItem $item) use ($entity) {
                        $this->handleInventoryUpdate(
                            $item->getOrderItem(),
                            null,
                            -$item->getQuantity(),
                            null,
                            $entity->getWarehouse(),
                            $entity
                        );
                    });
                }
            }
        }
    }

    /**
     * handle the inventory update for items which have been picked and packed
     * @param OrderItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param Warehouse $warehouse
     * @param Allocation $entity
     */
    protected function handleInventoryUpdate(
        $item,
        $inventoryUpdateQty,
        $allocatedInventoryQty,
        $message,
        $warehouse,
        Allocation $entity
    ) {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            null,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            $message,
            $entity->getOrder(),
            true
        );

        $context->setValue('warehouse', $warehouse);
        $this->eventDispatcher->dispatch(
            new InventoryUpdateEvent($context),
            InventoryUpdateEvent::NAME
        );

        $this->messageProducer->send(
            ResolveRebalanceInventoryTopic::getName(),
            [
                'product_id' => $item->getProduct()->getId(),
                'jobId' => $this->generateJobId($item->getProduct()->getId()),
                'priority' => MessagePriority::NORMAL
            ]
        );
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
     * @return array
     */
    protected function getDefaultWorkflowNames(): array
    {
        return [
            self::WORKFLOW_NAME
        ];
    }

    /**
     * @param mixed $context
     * @return bool
     */
    protected function isCorrectContext($context)
    {
        return ($context instanceof WorkflowItem
            && $context->getData() instanceof WorkflowData
            && $context->getData()->has(self::CONTEXT_KEY)
            && $context->getData()->get(self::CONTEXT_KEY) instanceof Allocation
        );
    }
}
