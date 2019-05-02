<?php

namespace Marello\Bundle\ShippingBundle\ExpressionLanguage;

use Marello\Bundle\ProductBundle\VirtualFields\VirtualFieldsProductDecoratorFactory;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItem;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;

class DecoratedProductLineItemFactory
{
    /**
     * @var VirtualFieldsProductDecoratorFactory
     */
    private $virtualFieldsProductDecoratorFactory;

    /**
     * @param VirtualFieldsProductDecoratorFactory $virtualFieldsProductDecoratorFactory
     */
    public function __construct(VirtualFieldsProductDecoratorFactory $virtualFieldsProductDecoratorFactory)
    {
        $this->virtualFieldsProductDecoratorFactory = $virtualFieldsProductDecoratorFactory;
    }

    /**
     * @param ShippingLineItemInterface[] $lineItems
     * @param ShippingLineItemInterface $lineItem
     *
     * @return ShippingLineItem
     */
    public function createLineItemWithDecoratedProductByLineItem(array $lineItems, ShippingLineItemInterface $lineItem)
    {
        $product = $lineItem->getProduct();

        $decoratedProduct = $product
            ? $this->virtualFieldsProductDecoratorFactory->createDecoratedProductByProductHolders($lineItems, $product)
            : null;

        return new ShippingLineItem(
            [
                ShippingLineItem::FIELD_PRICE => $lineItem->getPrice(),
                ShippingLineItem::FIELD_QUANTITY => $lineItem->getQuantity(),
                ShippingLineItem::FIELD_PRODUCT_HOLDER => $lineItem->getProductHolder(),
                ShippingLineItem::FIELD_PRODUCT_SKU => $lineItem->getProductSku(),
                ShippingLineItem::FIELD_WEIGHT => $lineItem->getWeight(),
                ShippingLineItem::FIELD_PRODUCT => $decoratedProduct,
            ]
        );
    }
}
