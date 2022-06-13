<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Workflow;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\Action\Event\ExtendableActionEvent;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\WorkflowBundle\Async\Topics;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;

class AllocationCompleteListener
{
    const TRANSIT_TO_STEP = 'ship';
    const WORKFLOW_NAME_B2C_1 = 'marello_order_b2c_workflow_1';
    const WORKFLOW_NAME_B2C_2 = 'marello_order_b2c_workflow_2';

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /** @var MessageProducerInterface $messageProducer */
    protected $messageProducer;

    /**use Oro\Bundle\WorkflowBundle\Model\Workflow;
     * AllocationWorkflowStartListener constructor.
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        WorkflowManager $workflowManager,
        MessageProducerInterface $messageProducer
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->workflowManager = $workflowManager;
        $this->messageProducer = $messageProducer;
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
        foreach ($order->getItems() as $item) {
            if ($item->getStatus() && $item->getStatus()->getId() === LoadOrderItemStatusData::SHIPPED) {
                $shippedItems[] = $item->getId();
            }
        }

        if (count($shippedItems) === $order->getItems()->count()) {
            // order is considered complete
            if ($workflow = $this->getApplicableWorkflow($entity->getOrder())) {
                /** @var WorkflowItem $workflowItem */
                $workflowItem = $this
                    ->doctrineHelper
                    ->getEntityRepositoryForClass(WorkflowItem::class)
                    ->findOneBy(
                        [
                            'entityId' => $entity->getOrder()->getId(),
                            'entityClass' => Order::class
                        ]
                    );
                $this->messageProducer->send(
                    Topics::WORKFLOW_TRANSIT_TOPIC,
                    [
                        'workflow_item_entity_id' => $entity->getOrder()->getId(),
                        'current_step_id' => $workflowItem->getCurrentStep()->getId(),
                        'entity_class' => Order::class,
                        'transition' => self::TRANSIT_TO_STEP,
                        'jobId' => md5($entity->getOrder()->getId()),
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
            self::WORKFLOW_NAME_B2C_1,
            self::WORKFLOW_NAME_B2C_2
        ];
    }
}
