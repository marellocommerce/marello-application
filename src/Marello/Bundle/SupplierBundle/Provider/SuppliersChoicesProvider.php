<?php

namespace Marello\Bundle\SupplierBundle\Provider;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class SuppliersChoicesProvider implements SuppliersChoicesProviderInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuppliers()
    {
        return array_reduce(
            $this->doctrineHelper
                ->getEntityManagerForClass(Supplier::class)
                ->getRepository(Supplier::class)
                ->findAll(),
            function (array $result, Supplier $supplier) {
                $label = $supplier->getName();
                $result[$label] = $supplier->getId();

                return $result;
            },
            []
        );
    }
}
