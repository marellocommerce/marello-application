<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowStartArguments;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;

class PurchaseOrderWorkflowTransitListener
{
    const WORKFLOW_NAME = 'marello_purchase_order_workflow';
    const TRANSIT_TO_STEP = 'send';

    /** @var array $entitiesScheduledForWorkflowStart*/
    private array $entitiesScheduledForWorkflowStart = [];

    /**
     * @param WorkflowManager $workflowManager
     */
    public function __construct(private WorkflowManager $workflowManager)
    {
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof PurchaseOrder) {
            $workflow = $this->getApplicableWorkflow($entity);
            if ($entity->getSupplier()->getPoSendBy() === Supplier::SEND_PO_MANUALLY) {
                $this->entitiesScheduledForWorkflowStart[] = new WorkflowStartArguments(
                    $workflow->getName(),
                    $entity,
                    [],
                    self::TRANSIT_TO_STEP
                );
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
