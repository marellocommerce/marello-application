<?php

namespace Marello\Bundle\PurchaseOrderBundle\EventListener\Datagrid;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
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
    public function buildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $productIdsToExclude = $this->getProductsIdsInPendingPurchaseOrders();

        $config->offsetAddToArrayByPath('source.query.where.and', [
            "p.id NOT IN (". $productIdsToExclude .")"
        ]);
    }

    /**
     * @param BuildBefore $event
     */
    public function buildBeforeDataIn(BuildBefore $event)
    {
        $config = $event->getConfig();

//        $dataIn = $event->getDatagrid()->getParameters()->get('data_in');
//        $dataInStr = implode(array_map('current', $dataIn), ',');
//        $dataInStr = "1,2,3";
//
//        $dataNotIn = $event->getDatagrid()->getParameters()->get('data_not_in');
//        $dataNotInStr = implode(array_map('current', $dataNotIn), ',');
//        $dataNotInStr = "4,5,6";
//
//
//        $config->offsetAddToArrayByPath('source.query.select', [
//            "CASE WHEN p.id IN (".$dataInStr.") AND p.id NOT IN (".$dataNotInStr.") THEN true ELSE false END AS hasProduct
//            "
//        ]);
//
//        $config->offsetSetByPath('source.bind_parameters',null);
    }

    /**
     * @param BuildBefore $event
     */
    public function buildBeforeFilter(BuildBefore $event)
    {
        $config = $event->getConfig();

        $supplier = $event->getDatagrid()->getParameters()->get('supplier');

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
