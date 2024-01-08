<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowStartArguments;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Model\WorkflowNameProviderInterface;

class OrderWorkflowStartListener
{
    const TRANSIT_TO_STEP = 'pending';

    /** @var WorkflowManager $workflowManager */
    private $workflowManager;

    /** @var array $entitiesScheduledForWorkflowStart */
    protected $entitiesScheduledForWorkflowStart = [];

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
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Order) {
            if ($workflow = $this->getApplicableWorkflow($entity)) {
                $this->entitiesScheduledForWorkflowStart[] = new WorkflowStartArguments(
                    $workflow->getName(),
                    $entity,
                    [],
                    self::TRANSIT_TO_STEP
                );
            }
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!empty($this->entitiesScheduledForWorkflowStart)) {
            $massStartData = $this->entitiesScheduledForWorkflowStart;
            unset($this->entitiesScheduledForWorkflowStart);
            $this->workflowManager->massStartWorkflow($massStartData);
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
            if (str_starts_with($name, WorkflowNameProviderInterface::MARELLO_WORKFLOW_START)) {
                $applicableWorkflows[$name] = $workflow;
            }
        }

        if (count($applicableWorkflows) !== 1) {
            return null;
        }

        return array_shift($applicableWorkflows);
    }
}
