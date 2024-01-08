<?php

namespace Marello\Bundle\POSBundle\EventListener\Workflow;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\Action\Event\ExtendableActionEvent;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use Marello\Bundle\InventoryBundle\Provider\AllocationContextInterface;
use Marello\Bundle\WorkflowBundle\Async\Topic\WorkflowTransitTopic;

class PosOrderAllocationWorkflowListener
{
    use JobIdGenerationTrait;

    const WORKFLOW_STEP_FROM = 'pending';
    const WORKFLOW_NAME = 'marello_allocate_workflow';
    const CONTEXT_KEY = 'allocation';
    const WORKFLOW_ITEM_COMPLETE = 'item_complete';

    /**
     * PosOrderAllocationWorkflowListener constructor.
     * @param WorkflowManager $workflowManager
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(
        protected WorkflowManager $workflowManager,
        protected MessageProducerInterface $messageProducer
    ) {
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
            if ($entity->getAllocationContext() &&
                $entity->getAllocationContext()->getId() === AllocationContextInterface::ALLOCATION_CONTEXT_CASH_CARRY
            ) {
                if ($event->getContext()->getCurrentStep()->getName() === self::WORKFLOW_STEP_FROM) {
                    $this->messageProducer->send(
                        WorkflowTransitTopic::getName(),
                        [
                            'workflow_item_entity_id' => $entity->getId(),
                            'current_step_id' => $event->getContext()->getCurrentStep()->getId(),
                            'entity_class' => Allocation::class,
                            'transition' => self::WORKFLOW_ITEM_COMPLETE,
                            'priority' => MessagePriority::NORMAL
                        ]
                    );
                }
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
