<?php

namespace Marello\Bundle\POSBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowStartArguments;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Model\WorkflowNameProviderInterface;
use Marello\Bundle\POSBundle\Migrations\Data\ORM\LoadSalesChannelPOSTypeData;

class OnPOSOrderCreateListener
{
    const TRANSIT_TO_STEP = 'processing';

    /** @var array $entitiesScheduledForWorkflowStart */
    protected array $entitiesScheduledForWorkflowStart = [];

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        private WorkflowManager $workflowManager
    ){
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof Order) {
            return;
        }

        if ($salesChannel = $entity->getSalesChannel()) {
            if ($salesChannel->getChannelType() &&
                $salesChannel->getChannelType()->getName() === LoadSalesChannelPOSTypeData::POS
            ) {
                $entity->setInvoicedAt(new \DateTime('now', new \DateTimeZone('UTC')));
                $existingData = $entity->getData();
                if (empty($existingData) || !isset($existingData[0]['amount'])) {
                    $entityData = [];
                    if (is_array($existingData)) {
                        $entityData = array_shift($existingData);
                    }

                    if (!isset($entityData['amount'])) {
                        $entityData['amount'] = $entity->getGrandTotal();
                        $existingData[] = $entityData;
                    }
                    $entity->setData($existingData);
                }

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
            if (str_contains($name, WorkflowNameProviderInterface::ORDER_POS_WORKFLOW)) {
                $applicableWorkflows[$name] = $workflow;
            }
        }

        return array_shift($applicableWorkflows);
    }
}
