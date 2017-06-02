<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Datagrid;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Doctrine\ORM\EntityManager;

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


    /**
     * @param BuildAfter $event
     */
    public function buildBeforeFilterSupplier(BuildAfter $event)
    {
        $config = $event->getDatagrid()->getConfig();

        $supplierId = $event->getDatagrid()->getParameters()->get('supplierId');

        if ($supplierId) {

            $supplier = $this->entityManager->getRepository('MarelloSupplierBundle:Supplier')->find($supplierId);

            if ($supplier) {
                $config->offsetAddToArrayByPath('filters', [
                    'default' => [
                        'preferredSupplier' => [
                            'value' => $supplier->getName(),
                            'type' => TextFilterType::TYPE_CONTAINS
                        ]
                    ]
                ]);
            }

        }
    }

    private function getProductsIdsInPendingPurchaseOrders()
    {
        $purchaseOrders = $this->entityManager->getRepository('MarelloPurchaseOrderBundle:PurchaseOrder')->findAll();
        $productsIds = array();

        foreach ($purchaseOrders as $purchaseOrder) {
            $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($purchaseOrder);
            foreach ($workflowItems as $workflowItem) {
                if ('pending' === $workflowItem->getCurrentStep()->getName()) {
                    foreach ($purchaseOrder->getItems() as $purchaseOrderItem) {
                        $productsIds[] = $purchaseOrderItem->getProduct()->getId();
                    }
                }
            }
        }
        return implode(',', $productsIds);
    }
}
