<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Marello\Bundle\OrderBundle\Entity\Order;

class OrderWorkflowStartListener
{
    const WORKFLOW = 'marello_order_b2c_workflow_1';
    const TRANSIT_TO_STEP = 'pending';

    /** @var WorkflowManager $workflowManager */
    private $workflowManager;

    /** @var string $orderId*/
    private $orderId;

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
        $entity = $args->getEntity();
        if ($entity instanceof Order) {
            $this->orderId = $entity->getId();
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->orderId) {
            $entityManager = $args->getEntityManager();
            /** @var Order $entity */
            $entity = $entityManager
                ->getRepository(Order::class)
                ->find($this->orderId);
            if ($entity) {
                $this->orderId = null;
                $this->workflowManager->startWorkflow(self::WORKFLOW, $entity, self::TRANSIT_TO_STEP);
            }
        }
    }
}
