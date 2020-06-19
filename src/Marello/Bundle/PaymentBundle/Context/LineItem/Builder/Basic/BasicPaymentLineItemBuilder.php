<?php

namespace Marello\Bundle\PaymentBundle\Context\LineItem\Builder\Basic;

use Marello\Bundle\PaymentBundle\Context\LineItem\Builder\PaymentLineItemBuilderInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;
use Oro\Bundle\CurrencyBundle\Entity\Price;

class BasicPaymentLineItemBuilder implements PaymentLineItemBuilderInterface
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
            PaymentLineItem::FIELD_QUANTITY => $this->quantity,
            PaymentLineItem::FIELD_PRODUCT_HOLDER => $this->productHolder,
            PaymentLineItem::FIELD_ENTITY_IDENTIFIER => $this->productHolder->getId(),
        ];

        if (null !== $this->product) {
            $params[PaymentLineItem::FIELD_PRODUCT] = $this->product;
        }

        if (null !== $this->productSku) {
            $params[PaymentLineItem::FIELD_PRODUCT_SKU] = $this->productSku;
        }

        if (null !== $this->weight) {
            $params[PaymentLineItem::FIELD_WEIGHT] = $this->weight;
        }

        if (null !== $this->price) {
            $params[PaymentLineItem::FIELD_PRICE] = $this->price;
        }

        return new PaymentLineItem($params);
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
