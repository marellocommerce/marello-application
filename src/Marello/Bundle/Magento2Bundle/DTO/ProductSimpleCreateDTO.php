<?php

namespace Marello\Bundle\Magento2Bundle\DTO;

use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\Magento2Bundle\Entity\AttributeSet;
use Marello\Bundle\Magento2Bundle\Entity\ProductTaxClass;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;

class ProductSimpleCreateDTO
{
    public const DEFAULT_PRICE = 0.0;
    public const TYPE_ID = 'simple';
    public const DEFAULT_ATTR_SET_ID = '4';

    /** @var string */
    protected $typeId = self::TYPE_ID;

    /** @var string */
    protected $attrSetID = self::DEFAULT_ATTR_SET_ID;

    /** @var Product */
    protected $product;

    /** @var float */
    protected $price;

    /** @var Website[] */
    protected $websites = [];

    /** @var ProductStatus */
    protected $status;

    /** @var InventoryItem */
    protected $inventoryItem;

    /** @var ProductTaxClass|null */
    protected $productTaxClass;

    /** @var BalancedInventoryLevel|null */
    protected $balancedInventoryLevel;

    /** @var AttributeSet|null */
    protected $attributeSet;

    /**
     * @param Product $product
     * @param Website[] $websites
     * @param ProductStatus $status
     * @param InventoryItem $inventoryItem
     * @param ProductTaxClass|null $productTaxClass
     * @param BalancedInventoryLevel|null $balancedInventoryLevel
     * @param AttributeSet|null $attributeSet
     */
    public function __construct(
        Product $product,
        array $websites,
        ProductStatus $status,
        InventoryItem $inventoryItem,
        ProductTaxClass $productTaxClass = null,
        BalancedInventoryLevel $balancedInventoryLevel = null,
        AttributeSet $attributeSet = null
    ) {
        $this->product = $product;
        $this->websites = $websites;
        $this->status = $status;
        $this->inventoryItem = $inventoryItem;
        $this->productTaxClass = $productTaxClass;
        $this->balancedInventoryLevel = $balancedInventoryLevel;
        $this->attributeSet = $attributeSet;
        $this->price = static::DEFAULT_PRICE;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
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

    /**
     * @return string
     */
    public function getTypeId(): string
    {
        return $this->typeId;
    }

    /**
     * @return string
     */
    public function getAttrSetID(): string
    {
        return ($this->attributeSet) ? (string)$this->attributeSet->getOriginId() : $this->attrSetID;
    }
}
