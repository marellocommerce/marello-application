<?php

namespace Marello\Bundle\ProductBundle\EventListener\Datagrid;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Doctrine\ORM\EntityManager;

class ProductSupplierGridListener
{
    /** @var EntityManager */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param BuildBefore $event
     */
    public function buildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $supplierId = $event->getDatagrid()->getParameters()->get('supplierId');

        $productIdsToInclude = $this->getProductsRelatedToSupplier($supplierId);

        $config->offsetAddToArrayByPath('source.query.where.and', [
            "p.id IN (". $productIdsToInclude .")"
        ]);

        //set null the parameter binding as it's not used in the query
        $config->offsetSetByPath('source.bind_parameters',null);
    }

    private function getProductsRelatedToSupplier($supplierId)
    {
        $productsIds = $this->entityManager->getRepository('MarelloSupplierBundle:ProductSupplierRelation')->getProductIdsRelatedToSupplier($supplierId);
        return $productsIds;
    }
}
