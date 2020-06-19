<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\Magento2Bundle\Entity\ProductTaxClass;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Marello\Bundle\Magento2Bundle\Entity\Product as InternalMagentoProduct;

class ProductSimpleUpdateDTO
{
    /** @var InternalMagentoProduct */
    protected $internalMagentoProduct;

    /** @var Product */
    protected $product;

    /** @var Website[] */
    protected $websites = [];

    /** @var ProductStatus */
    protected $status;

    /** @var InventoryItem */
    protected $inventoryItem;

    /** @var ProductTaxClass|null */
    protected $productTaxClass;

    /** @var BalancedInventoryLevel */
    protected $balancedInventoryLevel;

    /**
     * @param InternalMagentoProduct $internalMagentoProduct
     * @param Product $product
     * @param Website[] $websites
     * @param ProductStatus $status
     * @param InventoryItem $inventoryItem
     * @param ProductTaxClass|null $productTaxClass
     * @param BalancedInventoryLevel|null $balancedInventoryLevel
     */
    public function __construct(
        InternalMagentoProduct $internalMagentoProduct,
        Product $product,
        array $websites,
        ProductStatus $status,
        InventoryItem $inventoryItem,
        ProductTaxClass $productTaxClass = null,
        BalancedInventoryLevel $balancedInventoryLevel = null
    ) {
        $this->internalMagentoProduct = $internalMagentoProduct;
        $this->product = $product;
        $this->websites = $websites;
        $this->status = $status;
        $this->inventoryItem = $inventoryItem;
        $this->productTaxClass = $productTaxClass;
        $this->balancedInventoryLevel = $balancedInventoryLevel;
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
     * @return Website[]
     */
    public function getWebsites(): array
    {
        return $this->websites;
    }

    /**
     * @return ProductStatus
     */
    public function getStatus(): ProductStatus
    {
        return $this->status;
    }

    /**
     * @return InventoryItem
     */
    public function getInventoryItem(): InventoryItem
    {
        return $this->inventoryItem;
    }

    /**
     * @return ProductTaxClass|null
     */
    public function getProductTaxClass(): ?ProductTaxClass
    {
        return $this->productTaxClass;
    }

    /**
     * @return BalancedInventoryLevel|null
     */
    public function getBalancedInventoryLevel(): ?BalancedInventoryLevel
    {
        return $this->balancedInventoryLevel;
    }
}
