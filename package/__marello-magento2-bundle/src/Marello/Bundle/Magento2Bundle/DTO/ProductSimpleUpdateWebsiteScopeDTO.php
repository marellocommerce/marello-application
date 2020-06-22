<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\Magento2Bundle\Entity\Product as InternalMagentoProduct;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\ProductBundle\Entity\Product;

class ProductSimpleUpdateWebsiteScopeDTO
{
    /** @var InternalMagentoProduct */
    protected $internalMagentoProduct;

    /** @var Product */
    protected $product;

    /** @var Website */
    protected $website;

    /** @var float|null */
    protected $defaultPrice;

    /** @var float|null */
    protected $specialPrice;

    /**
     * @param InternalMagentoProduct $internalMagentoProduct
     * @param Product $product
     * @param Website $website
     * @param float|null $defaultPrice
     * @param float|null $specialPrice
     */
    public function __construct(
        InternalMagentoProduct $internalMagentoProduct,
        Product $product,
        Website $website,
        float $defaultPrice = null,
        float $specialPrice = null
    ) {
        $this->internalMagentoProduct = $internalMagentoProduct;
        $this->product = $product;
        $this->website = $website;
        $this->defaultPrice = $defaultPrice;
        $this->specialPrice = $specialPrice;
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
     * @return Website
     */
    public function getWebsite(): Website
    {
        return $this->website;
    }

    /**
     * @return float|null
     */
    public function getDefaultPrice(): ?float
    {
        return $this->defaultPrice;
    }

    /**
     * @return float|null
     */
    public function getSpecialPrice(): ?float
    {
        return $this->specialPrice;
    }

    /**
     * @return array
     */
    public function getErrorContext(): array
    {
        return [
            'product_id' => $this->product->getId(),
            'internal_magento_product_id' => $this->internalMagentoProduct->getId(),
            'website_id' => $this->getWebsite()->getId(),
        ];
    }
}
