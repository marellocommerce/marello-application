<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class ReplenishmentWorkflowAllocateInventoryListener
{
    const WORKFLOW = 'marello_replenishment_order_workflow';
    const TRANSITION = 'allocate_inventory';

    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @var array
     */
    private $replenishmentOrderIds = [];

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
        if ($entity instanceof ReplenishmentOrder && $entity->getExecutionDateTime() <= new \DateTime()) {
            $this->replenishmentOrderIds[] = $entity->getId();
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (count($this->replenishmentOrderIds) > 0) {
            $entityManager = $args->getObjectManager();
            foreach ($this->replenishmentOrderIds as $key => $replenishmentOrderId) {
                /** @var ReplenishmentOrder $entity */
                $entity = $entityManager
                    ->getRepository(ReplenishmentOrder::class)
                    ->find($replenishmentOrderId);
                if ($entity) {
                    unset($this->replenishmentOrderIds[$key]);
                    $this->transitTo($entity, self::WORKFLOW, self::TRANSITION);
                }
            }
        }
    }

    /**
     * @param ReplenishmentOrder $order
     * @param string $workflow
     * @param string $transition
     */
    private function transitTo(ReplenishmentOrder $order, $workflow, $transition)
    {
        $workflowItem = $this->getCurrentWorkFlowItem($order, $workflow);
        if (!$workflowItem) {
            return;
        }

        $this->workflowManager->transitIfAllowed($workflowItem, $transition);
    }

    /**
     * @param ReplenishmentOrder $order
     * @param string $workflow
     * @return null|WorkflowItem
     */
    private function getCurrentWorkFlowItem(ReplenishmentOrder $order, $workflow)
    {
        $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($order);
        if (0 !== count($workflowItems)) {
            /** @var WorkflowItem $workflowItem */
            $workflowItem = array_shift($workflowItems);
            //find the follow-up workflow
            if (preg_match('/'.$workflow.'/', $workflowItem->getWorkflowName())) {
                return $workflowItem;
            }
        }
        return null;
    }
}
