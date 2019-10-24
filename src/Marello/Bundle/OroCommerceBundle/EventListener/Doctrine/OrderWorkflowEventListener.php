<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OroCommerceBundle\ImportExport\Serializer\OrderNormalizer;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class OrderWorkflowEventListener
{
    const WORKFLOW = 'marello_order_b2c_workflow_1';
    const TRANSIT_TO_STEP = 'invoice';
    
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @var string
     */
    private $orderReference;

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
            $orderData = $entity->getData();
            if (isset($orderData[OrderNormalizer::PAYMENT_STATUS])) {
                $paymentStatus = $orderData[OrderNormalizer::PAYMENT_STATUS];
                if ($paymentStatus === OrderNormalizer::PAID_FULLY_STATUS) {
                    $this->orderReference = $entity->getOrderReference();
                }
            }
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->orderReference) {
            $entityManager = $args->getEntityManager();
            $entity = $entityManager
                ->getRepository(Order::class)
                ->findOneBy(['orderReference' => $this->orderReference]);

            if ($entity) {
                $this->orderReference = null;
                $orderData = $entity->getData();
                if (isset($orderData[OrderNormalizer::PAYMENT_STATUS])) {
                    $paymentStatus = $orderData[OrderNormalizer::PAYMENT_STATUS];
                    if ($paymentStatus === OrderNormalizer::PAID_FULLY_STATUS) {
                        $this->transitTo($entity, self::WORKFLOW, self::TRANSIT_TO_STEP);
                    }
                }
            }
        }
    }

    /**
     * @param Order $order
     * @param string $workflow
     * @param string $transition
     */
    private function transitTo(Order $order, $workflow, $transition)
    {
        $workflowItem = $this->getCurrentWorkFlowItem($order, $workflow);
        if (!$workflowItem) {
            return;
        }

        $this->workflowManager->transitIfAllowed($workflowItem, $transition);
    }

    /**
     * @param Order $order
     * @param string $workflow
     * @return null|WorkflowItem
     */
    private function getCurrentWorkFlowItem(Order $order, $workflow)
    {
        $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($order);
        if (0 !== count($workflowItems)) {
            /** @var WorkflowItem $workflowItem */
            $workflowItem = array_shift($workflowItems);
            //find the follow-up workflow
            if (preg_match('/'.$workflow.'/', $workflowItem->getWorkflowName())) {
                return $workflowItem; //->getCurrentStep()->getName();
            }
        }
        return null;
    }
}
