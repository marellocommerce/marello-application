<?php

namespace Marello\Bundle\InventoryBundle\EventListener\Workflow;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowStartArguments;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\NotificationMessageBundle\Event\CreateNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory;
use Marello\Bundle\NotificationMessageBundle\Provider\NotificationMessageSourceInterface;

class AllocationWorkflowStartListener
{
    const TRANSIT_TO_STEP = 'pending';
    const WORKFLOW_NAME = 'marello_allocate_workflow';

    /** @var WorkflowManager $workflowManager */
    private $workflowManager;

    /** @var EventDispatcherInterface $eventDispatcher */
    private $eventDispatcher;

    /** @var array $entitiesScheduledForWorkflowStart */
    protected $entitiesScheduledForWorkflowStart = [];

    protected $entities = [];
    /**
     * AllocationWorkflowStartListener constructor.
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        WorkflowManager $workflowManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->workflowManager = $workflowManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Allocation) {
            if ($workflow = $this->getApplicableWorkflow($entity)) {
                $this->entities[] = $entity;
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

        if (!empty($this->entities)) {
            $entities = $this->entities;
            unset($this->entities);
            /** @var Allocation $entity */
            foreach ($entities as $entity) {
                if ($entity->getStatus() &&
                    $entity->getStatus()->getId() === AllocationStateStatusInterface::ALLOCATION_STATUS_CNA
                ) {
                    $context = NotificationMessageContextFactory::createWarning(
                        NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_ALLOCATION,
                        'marello.notificationmessage.allocation.no_available.title',
                        'marello.notificationmessage.allocation.no_available.message',
                        'marello.notificationmessage.allocation.no_available.solution',
                        $entity,
                        'allocating'
                    );
                    $this->eventDispatcher->dispatch(
                        new CreateNotificationMessageEvent($context),
                        CreateNotificationMessageEvent::NAME
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
}
