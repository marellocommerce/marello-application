<?php

namespace Marello\Bundle\ProductBundle\Event;

use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Symfony\Contracts\EventDispatcher\Event;

class ProductDropshipEvent extends Event
{
    const NAME = 'marello_product.product_dropship_toggle';

    /**
     * @var ProductSupplierRelation
     */
    protected $productSupplierRelation;

    /**
     * @var boolean
     */
    protected $canDropship;

    /**
     * @param ProductSupplierRelation $productSupplierRelation
     * @param bool $canDropship
     */
    public function __construct(ProductSupplierRelation $productSupplierRelation, $canDropship)
    {
        $this->productSupplierRelation = $productSupplierRelation;
        $this->canDropship = $canDropship;
    }

    /**
     * @return ProductSupplierRelation
     */
    public function getProductSupplierRelation()
    {
        return $this->productSupplierRelation;
    }

    /**
     * @return bool
     */
    public function isCanDropship()
    {
        return $this->canDropship;
    }
}
