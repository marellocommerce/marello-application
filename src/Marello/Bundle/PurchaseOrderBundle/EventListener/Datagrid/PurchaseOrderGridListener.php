<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Datagrid;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class PurchaseOrderGridListener
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var WorkflowManager */
    protected $workflowManager;

    public function __construct(EntityManager $entityManager, WorkflowManager $workflowManager)
    {
        $this->entityManager = $entityManager;
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param BuildBefore $event
     */
    public function buildBeforePendingOrders(BuildBefore $event)
    {
        $config = $event->getConfig();

        $productIdsToExclude = $this->getProductsIdsInPendingPurchaseOrders();

        if ($productIdsToExclude != '') {
            $config->offsetAddToArrayByPath('source.query.where.and', [
                "p.id NOT IN (". $productIdsToExclude .")"
            ]);
        }
    }

    private function getProductsIdsInPendingPurchaseOrders()
    {
        $purchaseOrders = $this->entityManager->getRepository('MarelloPurchaseOrderBundle:PurchaseOrder')->findAll();
        $productsIds = array();

        foreach ($purchaseOrders as $purchaseOrder) {
            $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($purchaseOrder);
            foreach ($workflowItems as $workflowItem) {
                if (in_array($workflowItem->getCurrentStep()->getName(), ['not_sent', 'pending'])) {
                    foreach ($purchaseOrder->getItems() as $purchaseOrderItem) {
                        $productsIds[] = $purchaseOrderItem->getProduct()->getId();
                    }
                }
            }
        }
        return implode(',', $productsIds);
    }
}
