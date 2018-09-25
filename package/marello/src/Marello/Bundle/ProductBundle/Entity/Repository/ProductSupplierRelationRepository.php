<?php

namespace Marello\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\SupplierBundle\Entity\Supplier;

class ProductSupplierRelationRepository extends EntityRepository
{
    /**
     * Returns the product ids related to a given Supplier
     *
     * @param Supplier $supplier
     * @return string
     */
    public function getProductIdsRelatedToSupplier(Supplier $supplier)
    {
        $qb = $this->createQueryBuilder('psr');
        $qb->select('p.id')
            ->leftJoin('psr.product', 'p')
            ->where('psr.supplier = :supplierId')
            ->setParameter('supplierId', $supplier->getId())
            ->groupBy('p.id')
            ;

        $query = $qb->getQuery();
        return implode(array_map('current', $query->getArrayResult()), ',');
    }
}
