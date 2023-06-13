<?php

namespace Marello\Bundle\PurchaseOrderBundle\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;

class PurchaseOrderCandidatesProvider
{
    /** @var EntityManager $entityManager */
    protected EntityManager $entityManager;

    /** @var WorkflowManager $workflowManager */
    protected WorkflowManager $workflowManager;

    /** @var AclHelper $aclHelper */
    protected AclHelper $aclHelper;

    public function __construct(
        EntityManager $entityManager,
        WorkflowManager $workflowManager,
        AclHelper $aclHelper
    ) {
        $this->entityManager = $entityManager;
        $this->workflowManager = $workflowManager;
        $this->aclHelper = $aclHelper;
    }

    /**
     * Get Product ids that are in currently pending or not_sent purchase orders
     * in order to be able to exclude these ids from Purchase Order candidates.
     * @return string
     */
    public function getProductsIdsInPendingPurchaseOrders(): string
    {
        $purchaseOrders = $this->entityManager->getRepository(PurchaseOrder::class)->findAll();
        $productsIds = [];
        /** @var PurchaseOrder $purchaseOrder */
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

    /**
     * Get Purchase Order candidates
     * @return array
     */
    public function getPurchaseOrderCandidates(): array
    {
        $productIdsToExclude = $this->getProductsIdsInPendingPurchaseOrders();
        return $this
            ->entityManager
            ->getRepository(Product::class)
            ->getPurchaseOrderItemsCandidates($this->aclHelper, $productIdsToExclude);
    }
}
