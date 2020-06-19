<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\Magento2Bundle\Entity\Product as InternalMagentoProduct;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductDeleteOnChannelDTO
{
    /** @var InternalMagentoProduct */
    protected $internalMagentoProduct;

    /** @var Product */
    protected $product;

    /**
     * @param InternalMagentoProduct $internalMagentoProduct
     * @param Product $product
     */
    public function __construct(
        InternalMagentoProduct $internalMagentoProduct,
        Product $product
    ) {
        $this->internalMagentoProduct = $internalMagentoProduct;
        $this->product = $product;
    }

    /**
     * @return InternalMagentoProduct
     */
    public function getInternalMagentoProduct(): InternalMagentoProduct
    {
        return $this->internalMagentoProduct;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }
}