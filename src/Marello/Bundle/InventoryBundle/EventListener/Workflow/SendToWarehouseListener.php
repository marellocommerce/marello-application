<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Workflow;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\Action\Event\ExtendableActionEvent;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Model\Allocation\WarehouseNotifierRegistry;
use Marello\Bundle\InventoryBundle\Model\Allocation\WarehouseNotifierInterface;

class SendToWarehouseListener
{
    const WORKFLOW_NAME = 'marello_allocate_workflow';
    const CONTEXT_KEY = 'allocation';

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /** @var WarehouseNotifierRegistry $notifierRegistry */
    protected $notifierRegistry;

    /**
     * SendToWarehouseListener constructor.
     * @param WorkflowManager $workflowManager
     * @param WarehouseNotifierRegistry $notifierRegistry
     */
    public function __construct(
        WorkflowManager $workflowManager,
        WarehouseNotifierRegistry $notifierRegistry
    ) {
        $this->workflowManager = $workflowManager;
        $this->notifierRegistry = $notifierRegistry;
    }

    /**
     * @param ExtendableActionEvent $event
     * @throws \Exception
     */
    public function onSendTransitionAfter(ExtendableActionEvent $event)
    {
        if (!$this->isCorrectContext($event->getContext())) {
            return;
        }

        /** @var Allocation $entity */
        $entity = $event->getContext()->getData()->get(self::CONTEXT_KEY);
        if ($this->getApplicableWorkflow($entity)) {
            $selectedWarehouse = $entity->getWarehouse();
            $notifierId = $selectedWarehouse->getNotifier();
            /** @var WarehouseNotifierInterface|null $notifier */
            $notifier = $this->notifierRegistry->getNotifier($notifierId);
            if (!$notifier) {
                throw new \Exception(sprintf('NotifierId %s not found in notifier registry', $notifierId));
            }

            $notifier->notifyWarehouse($entity);
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
