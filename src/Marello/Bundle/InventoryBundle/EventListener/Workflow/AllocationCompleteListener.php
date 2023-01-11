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
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use Marello\Bundle\OrderBundle\Model\OrderItemStatusesInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryTotalCalculator;

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

    /** @var InventoryTotalCalculator $totalCalculator */
    protected $totalCalculator;

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
                    $orderItemQty += $allocationItem->getQuantityConfirmed();
                }

                if ($orderItemQty == $item->getQuantity()) {
                    $shippedItems[] = $item->getId();
                }
            }
        }

        // shipped items take quantities in account from above
        if (count($shippedItems) === $order->getItems()->count()) {
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
                    Topics::WORKFLOW_TRANSIT_TOPIC,
                    [
                        'workflow_item_entity_id' => $order->getId(),
                        'current_step_id' => $workflowItem->getCurrentStep()->getId(),
                        'entity_class' => Order::class,
                        'transition' => self::TRANSIT_TO_STEP,
                        'jobId' => md5($order->getId()),
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
