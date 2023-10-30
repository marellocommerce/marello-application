<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

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
        $entity = $args->getObject();
        if ($entity instanceof PurchaseOrder) {
            if ($entity->getSupplier()->getPoSendBy() === Supplier::SEND_PO_MANUALLY) {
                $this->purchaseOrderId = $entity->getId();
            } else {
                $onDemandItems = [];
                /** @var PurchaseOrderItem[] $poItems */
                $poItems = $entity->getItems()->toArray();
                foreach ($poItems as $poItem) {
                    if ($this->isOrderOnDemandAllowed($poItem->getProduct())) {
                        $onDemandItems[] = $poItem;
                    }
                }
                if (count($onDemandItems) === count($poItems)) {
                    $this->purchaseOrderId = $entity->getId();
                }
            }
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->purchaseOrderId) {
            $entityManager = $args->getObjectManager();
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

    /**
     * @param Product $product
     * @return bool
     */
    private function isOrderOnDemandAllowed(Product $product)
    {
        $inventoryItem = $product->getInventoryItem();
        if ($inventoryItem && $inventoryItem->isOrderOnDemandAllowed()) {
            return true;
        }

        return false;
    }
}
