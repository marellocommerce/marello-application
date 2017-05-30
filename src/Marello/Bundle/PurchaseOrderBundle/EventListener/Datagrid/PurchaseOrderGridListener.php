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

        if ($params && is_array($params) && key_exists('data_in', $params) && key_exists('data_out', $params)) {
            $dataIn = $params['data_in'];
            $dataNotIn = $params['data_not_in'];

            if (is_array($dataIn) && is_array($dataNotIn)) {
                $dataInStr = implode($dataIn, ',');
                $dataNotInStr = implode($dataNotIn, ',');

                if ($dataInStr != '' || $dataNotInStr != '') {

                    $config->offsetAddToArrayByPath('source.query.select', [
                        "(CASE WHEN p.id IN (".$dataInStr.") AND p.id NOT IN (".$dataNotInStr.") THEN true ELSE false END) AS hasProduct
                "
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
