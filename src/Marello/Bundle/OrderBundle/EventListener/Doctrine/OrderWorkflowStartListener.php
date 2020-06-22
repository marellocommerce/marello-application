<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Model\WorkflowNameProviderInterface;

class OrderWorkflowStartListener
{
    const TRANSIT_TO_STEP = 'pending';

    /** @var WorkflowManager $workflowManager */
    private $workflowManager;

    /** @var string $orderId*/
    private $orderId;

    /** @var DoctrineHelper $doctrineHelper */
    private $doctrineHelper;

    /**
     * @param WorkflowManager $workflowManager
     */
    public function __construct(WorkflowManager $workflowManager)
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof Order) {
            $this->orderId = $entity->getId();
        }
    }

    /**
     * @param PostFlushEventArgs $args
     * @throws \Oro\Bundle\WorkflowBundle\Exception\WorkflowRecordGroupException
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->orderId) {
            $entityManager = $args->getEntityManager();
            /** @var Order $entity */
            $entity = $this->doctrineHelper
                ->getEntityManagerForClass(Order::class)
                ->getRepository(Order::class)
                ->find($this->orderId);
            if ($entity && $workflow = $this->getApplicableWorkflow($entity)) {
                $this->orderId = null;
                $this->workflowManager->startWorkflow($workflow->getName(), $entity, self::TRANSIT_TO_STEP);
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
            WorkflowNameProviderInterface::ORDER_WORKFLOW_1,
            WorkflowNameProviderInterface::ORDER_WORKFLOW_2
        ];
    }

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }
}
