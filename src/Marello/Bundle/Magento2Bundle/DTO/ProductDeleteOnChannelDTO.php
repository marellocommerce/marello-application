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

    /** @var array $originWebsiteIds */
    protected $originWebsiteIds;

    /**
     * @param InternalMagentoProduct $internalMagentoProduct
     * @param Product $product
     * @param array $originWebsiteIds
     */
    public function __construct(
        InternalMagentoProduct $internalMagentoProduct,
        Product $product,
        array $originWebsiteIds = null
    ) {
        $this->internalMagentoProduct = $internalMagentoProduct;
        $this->product = $product;
        $this->originWebsiteIds = $originWebsiteIds;
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

    /**
     * @return array|null
     */
    public function getOriginWebsiteIds(): ?array
    {
        return $this->originWebsiteIds;
    }
}
