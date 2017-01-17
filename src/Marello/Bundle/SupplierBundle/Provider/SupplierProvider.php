<?php

namespace Marello\Bundle\SupplierBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SupplierBundle\Entity\ProductSupplierRelation;

class SupplierProvider
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * SupplierProvider constructor.
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Returns ids of all related sales channels for a product.
     *
     * @param Product $product
     *
     * @return array $ids
     */
    public function getProductSuppliersIds(Product $product)
    {
        $ids = [];
        $product
            ->getSuppliers()
            ->map(function (ProductSupplierRelation $productSupplierRelation) use (&$ids) {
                $ids[] = $productSupplierRelation->getId();
            });

        return $ids;
    }
}
