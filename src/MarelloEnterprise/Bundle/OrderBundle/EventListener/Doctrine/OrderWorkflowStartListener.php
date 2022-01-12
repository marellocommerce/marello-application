<?php

namespace MarelloEnterprise\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowStartArguments;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Model\WorkflowNameProviderInterface;
use Marello\Bundle\OrderBundle\EventListener\Doctrine\OrderWorkflowStartListener as BaseListener;

class OrderWorkflowStartListener
{
    const TRANSIT_TO_STEP = 'pending';

    /** @var WorkflowManager $workflowManager */
    private $workflowManager;

    /** @var array $entitiesScheduledForWorkflowStart */
    protected $entitiesScheduledForWorkflowStart = [];

    /** @var TokenAccessorInterface $tokenAccessor */
    private $tokenAccessor;

    /** @var BaseListener $baseListener */
    private $baseListener;

    /**
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        WorkflowManager $workflowManager,
        BaseListener $baseListener
    ) {
        $this->workflowManager = $workflowManager;
        $this->baseListener = $baseListener;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
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

        // prevent workflow from starting as this will trigger an error during permission 'bug/feature' creating
        // workflows from Global Access Org in a non global access org
        $organization = $this->tokenAccessor->getOrganization();
        if ($organization && $organization->getIsGlobal() && $entity->getOrganization() !== $organization) {
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
            WorkflowNameProviderInterface::ORDER_WORKFLOW_2,
            WorkflowNameProviderInterface::ORDER_DEPRECATED_WORKFLOW_1,
            WorkflowNameProviderInterface::ORDER_DEPRECATED_WORKFLOW_2
        ];
    }

    /**
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function setTokenAccessor(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }
}
