<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\EventListener\Workflow;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Entity\AllocationItem;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderManualItemConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\ManualReplenishmentStrategy;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Component\Action\Event\ExtendableActionEvent;

class TransitionEventListener
{
    const WORKFLOW_NAME = 'marello_allocate_workflow';
    const CONTEXT_KEY = 'allocation';

    /** @var EntityManagerInterface */
    protected $em;

    public function __construct(
        protected DoctrineHelper $doctrineHelper,
        protected WorkflowManager $workflowManager
    ) {}

    public function onSendTransitionAfter(ExtendableActionEvent $event): void
    {
        if (!$this->isCorrectContext($event->getContext())) {
            return;
        }

        /** @var Allocation $entity */
        $entity = $event->getContext()->getData()->get(self::CONTEXT_KEY);
        if (!$this->getApplicableWorkflow($entity)) {
            return;
        }

        if (!$entity->getParent() || !$entity->getChildren()->isEmpty()) {
            return;
        }

        // sub allocation, so it needs to create a replenishment order
        $items = $entity->getItems();
        // destination is the consolidation warehouse
        $destinationWarehouse = $entity->getParent()->getWarehouse();
        $originWarehouse = $entity->getWarehouse();

        $replOrderConfig = new ReplenishmentOrderConfig();
        $replOrderConfig->setStrategy(ManualReplenishmentStrategy::IDENTIFIER);
        $replOrderConfig->setOrganization($entity->getOrganization());

        $replOrder = new ReplenishmentOrder();
        $replOrder->setOrigin($originWarehouse);
        $replOrder->setDestination($destinationWarehouse);
        $replOrder->setDescription(
            sprintf('Replenishment for consolidation of Allocation %s', $entity->getParent()->getAllocationNumber())
        );
        $replOrder->setReplOrderConfig($replOrderConfig);
        $replOrder->setOrganization($entity->getOrganization());
        $items->map(function (AllocationItem $item) use (
            $destinationWarehouse,
            $originWarehouse,
            $replOrder,
            $replOrderConfig
        ) {
            $qty = $item->getQuantity();

            $manualItemConfig = new ReplenishmentOrderManualItemConfig();
            $manualItemConfig
                ->setOrderConfig($replOrderConfig)
                ->setDestination($destinationWarehouse)
                ->setOrigin($originWarehouse)
                ->setProduct($item->getProduct())
                ->setAllQuantity(true)
                ->setQuantity($qty)
                ->setAvailableQuantity($qty);
            $replOrderConfig->addManualItem($manualItemConfig);

            $replItem = new ReplenishmentOrderItem();
            $replItem
                ->setOrder($replOrder)
                ->setProduct($item->getProduct())
                ->setAllQuantity(true)
                ->setInventoryQty($qty)
                ->setTotalInventoryQty($qty);
            $replOrder->addReplOrderItem($replItem);
        });

        $this->getEntityManager()->persist($replOrderConfig);
        $this->getEntityManager()->persist($replOrder);
        $this->getEntityManager()->flush();
    }

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

    protected function getDefaultWorkflowNames(): array
    {
        return [
            self::WORKFLOW_NAME
        ];
    }

    protected function isCorrectContext($context): bool
    {
        return ($context instanceof WorkflowItem
            && $context->getData() instanceof WorkflowData
            && $context->getData()->has(self::CONTEXT_KEY)
            && $context->getData()->get(self::CONTEXT_KEY) instanceof Allocation
        );
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        if (!$this->em) {
            $this->em = $this->doctrineHelper->getEntityManagerForClass(ReplenishmentOrder::class);
        }

        return $this->em;
    }
}
