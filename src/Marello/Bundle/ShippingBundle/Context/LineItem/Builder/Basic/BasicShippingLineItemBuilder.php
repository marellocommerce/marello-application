<?php

namespace Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Basic;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;
use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\ShippingLineItemBuilderInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItem;
use Oro\Bundle\CurrencyBundle\Entity\Price;

class BasicShippingLineItemBuilder implements ShippingLineItemBuilderInterface
{
    /**
     * @var Price
     */
    private $price;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var ProductAwareInterface
     */
    private $productHolder;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var string
     */
    private $productSku;

    /**
     * @var float
     */
    private $weight;

    /**
     * @param int $quantity
     * @param ProductAwareInterface $productHolder
     */
    public function __construct(
        $quantity,
        ProductAwareInterface $productHolder
    ) {
        $this->quantity = $quantity;
        $this->productHolder = $productHolder;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult()
    {
        $params = [
            ShippingLineItem::FIELD_QUANTITY => $this->quantity,
            ShippingLineItem::FIELD_PRODUCT_HOLDER => $this->productHolder,
            ShippingLineItem::FIELD_ENTITY_IDENTIFIER => $this->productHolder->getId(),
        ];

        if (null !== $this->product) {
            $params[ShippingLineItem::FIELD_PRODUCT] = $this->product;
        }

        if (null !== $this->productSku) {
            $params[ShippingLineItem::FIELD_PRODUCT_SKU] = $this->productSku;
        }

        if (null !== $this->weight) {
            $params[ShippingLineItem::FIELD_WEIGHT] = $this->weight;
        }

        if (null !== $this->price) {
            $params[ShippingLineItem::FIELD_PRICE] = $this->price;
        }

        return new ShippingLineItem($params);
    }

    /**
     * {@inheritDoc}
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setProductSku($sku)
    {
        $this->productSku = $sku;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPrice(Price $price)
    {
        $this->price = $price;

        return $this;
    }
}
