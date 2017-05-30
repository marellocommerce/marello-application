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
     * @param BuildBefore $event
     */
    public function buildBeforeDataIn(BuildBefore $event)
    {
        $config = $event->getConfig();

        $params = $event->getDatagrid()->getParameters()->get('_parameters');

        if ($params && is_array($params) && key_exists('data_in', $params) && key_exists('data_not_in', $params)) {
            $dataIn = $params['data_in'];
            $dataNotIn = $params['data_not_in'];

            if (is_array($dataIn) && is_array($dataNotIn)) {
                $dataInStr = implode($dataIn, ',');
                $dataNotInStr = implode($dataNotIn, ',');

                if ($dataInStr != '') {
                    $config->offsetAddToArrayByPath('source.query.where.and', [
                        "p.id NOT IN (". $dataInStr .")"
                    ]);
                }

                if ($dataNotInStr != '') {
                    $config->offsetAddToArrayByPath('source.query.where.and', [
                        "p.id IN (". $dataNotInStr .")"
                    ]);
                }
            }
        }
    }

    /**
     * @param BuildBefore $event
     */
    public function buildBeforeFilterSupplier(BuildBefore $event)
    {
        $config = $event->getConfig();

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
