<?php

namespace Marello\Bundle\ProductBundle\EventListener\Datagrid;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class ProductSupplierGridListener
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param BuildBefore $event
     */
    public function buildBeforeProductsBySupplier(BuildBefore $event)
    {
        $config = $event->getConfig();

        $supplier = $event->getDatagrid()->getParameters()->get('supplier');

        if (!$supplier) {
            $supplierId = $event->getDatagrid()->getParameters()->get('supplierId');

            if ($supplierId) {
                /** @var Supplier $supplier */
                $supplier = $this->entityManager->getRepository('MarelloSupplierBundle:Supplier')->find($supplierId);
            }
        }

        if ($supplier) {
            $productIdsToInclude = $this->getProductsRelatedToSupplier($supplier);

            $config->offsetAddToArrayByPath('source.query.where.and', [
                "p.id IN (". $productIdsToInclude .")"
            ]);
        }
    }

    /**
     * @param Supplier $supplier
     * @return mixed
     */
    private function getProductsRelatedToSupplier(Supplier $supplier)
    {
        $productsIds = $this->entityManager
            ->getRepository('MarelloProductBundle:ProductSupplierRelation')
            ->getProductIdsRelatedToSupplier($supplier);
        return $productsIds;
    }
}
