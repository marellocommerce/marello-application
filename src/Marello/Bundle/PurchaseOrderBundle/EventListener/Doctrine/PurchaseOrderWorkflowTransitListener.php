<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;

class PurchaseOrderWorkflowTransitListener
{
    const WORKFLOW = 'marello_purchase_order_workflow';
    const TRANSIT_TO_STEP = 'send';

    /** @var WorkflowManager $workflowManager */
    private $workflowManager;

    /** @var string $purchaseOrderId*/
    private $purchaseOrderId;

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
        if ($entity instanceof PurchaseOrder && $entity->getSupplier()->getPoSendBy() === Supplier::SEND_PO_MANUALLY) {
            $this->purchaseOrderId = $entity->getId();
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->purchaseOrderId) {
            $entityManager = $args->getEntityManager();
            /** @var PurchaseOrder $entity */
            $entity = $entityManager
                ->getRepository(PurchaseOrder::class)
                ->find($this->purchaseOrderId);
            if ($entity) {
                $this->purchaseOrderId = null;
                $this->transitTo($entity, self::WORKFLOW, self::TRANSIT_TO_STEP);
            }
        }
    }

    /**
     * @param PurchaseOrder $order
     * @param string $workflow
     * @param string $transition
     */
    private function transitTo(PurchaseOrder $order, $workflow, $transition)
    {
        $workflowItem = $this->getCurrentWorkFlowItem($order, $workflow);
        if (!$workflowItem) {
            return;
        }

        $this->workflowManager->transitIfAllowed($workflowItem, $transition);
    }

    /**
     * @param PurchaseOrder $order
     * @param string $workflow
     * @return null|WorkflowItem
     */
    private function getCurrentWorkFlowItem(PurchaseOrder $order, $workflow)
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
