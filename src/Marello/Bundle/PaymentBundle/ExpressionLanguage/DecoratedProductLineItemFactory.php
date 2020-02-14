<?php

namespace Marello\Bundle\PaymentBundle\ExpressionLanguage;

use Marello\Bundle\PaymentBundle\Context\PaymentLineItem;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItemInterface;
use Marello\Bundle\ProductBundle\VirtualFields\VirtualFieldsProductDecoratorFactory;

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
     * @param PaymentLineItemInterface[] $lineItems
     * @param PaymentLineItemInterface $lineItem
     *
     * @return PaymentLineItem
     */
    public function createLineItemWithDecoratedProductByLineItem(array $lineItems, PaymentLineItemInterface $lineItem)
    {
        $product = $lineItem->getProduct();

        $decoratedProduct = $product
            ? $this->virtualFieldsProductDecoratorFactory->createDecoratedProductByProductHolders($lineItems, $product)
            : null;

        return new PaymentLineItem(
            [
                PaymentLineItem::FIELD_PRICE => $lineItem->getPrice(),
                PaymentLineItem::FIELD_QUANTITY => $lineItem->getQuantity(),
                PaymentLineItem::FIELD_PRODUCT_HOLDER => $lineItem->getProductHolder(),
                PaymentLineItem::FIELD_PRODUCT_SKU => $lineItem->getProductSku(),
                PaymentLineItem::FIELD_PRODUCT => $decoratedProduct,
            ]
        );
    }
}
