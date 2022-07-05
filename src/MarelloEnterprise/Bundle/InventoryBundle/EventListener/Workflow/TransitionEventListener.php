<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Workflow;

use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\Action\Event\ExtendableActionEvent;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;

class TransitionEventListener
{
    const WORKFLOW_NAME = 'marello_allocate_workflow';
    const CONTEXT_KEY = 'allocation';

    /** @var DoctrineHelper $doctrineHelper*/
    protected $doctrineHelper;

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /**
     * TransitionEventListener constructor.
     * @param DoctrineHelper $doctrineHelper
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        WorkflowManager $workflowManager
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param ExtendableActionEvent $event
     * @throws \Exception
     */
    public function onSendTransitionAfter(ExtendableActionEvent $event)
    {
        if (!$this->isCorrectContext($event->getContext())) {
            return;
        }

        /** @var Allocation $entity */
        $entity = $event->getContext()->getData()->get(self::CONTEXT_KEY);

        if ($this->getApplicableWorkflow($entity)) {
            if ($entity->getParent() && $entity->getChildren()->isEmpty()) {
                // sub allocation, so it needs to create a replenishment order
                $items = $entity->getItems();
                $products = [];
                $consolidationWarehouse = $entity->getParent()->getWarehouse();
                $originWarehouse = $entity->getWarehouse();

                $replOrderConfig = new ReplenishmentOrderConfig();
                $replOrderConfig->setStrategy('equal_division');
                $replOrderConfig->setOrigins([$originWarehouse->getId()]);
                // destination is the consolidation warehouse
                $replOrderConfig->setDestinations([$consolidationWarehouse->getId()]);
                $replOrderConfig->setProducts($products);
                $replOrderConfig->setPercentage(100);
                $replOrderConfig->setOrganization($entity->getOrganization());
                $replcfgEm = $this->doctrineHelper->getEntityManagerForClass(ReplenishmentOrderConfig::class);
                $replcfgEm->persist($replOrderConfig);

                $replOrder = new ReplenishmentOrder();
                $replOrder->setOrigin($entity->getWarehouse());
                $replOrder->setDestination($consolidationWarehouse);
                $replOrder->setDescription(
                    sprintf('Replenishment for consolidation of Allocation %s', $entity->getParent()->getAllocationNumber())
                );
                $replOrder->setReplOrderConfig($replOrderConfig);
                $replOrder->setPercentage(100);
                $replOrder->setOrganization($entity->getOrganization());
                $items->map(function (AllocationItem $item) use ($consolidationWarehouse, $replOrder) {
                    // create replenishment item for the
                    $replItem = new ReplenishmentOrderItem();
                    $replItem->setProduct($item->getProduct());
                    $replItem->setOrder($replOrder);
                    $replItem->setInventoryQty($item->getQuantity());
                    $replItem->setTotalInventoryQty($item->getQuantity());
                    $replOrder->addReplOrderItem($replItem);
                });

                $replEm = $this->doctrineHelper->getEntityManagerForClass(ReplenishmentOrder::class);
                $replEm->persist($replOrder);
                $replEm->flush();
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

    /**
     * @param mixed $context
     * @return bool
     */
    protected function isCorrectContext($context)
    {
        return ($context instanceof WorkflowItem
            && $context->getData() instanceof WorkflowData
            && $context->getData()->has(self::CONTEXT_KEY)
            && $context->getData()->get(self::CONTEXT_KEY) instanceof Allocation
        );
    }
}
