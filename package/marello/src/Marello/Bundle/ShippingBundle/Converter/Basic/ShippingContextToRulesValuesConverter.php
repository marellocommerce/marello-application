<?php

namespace Marello\Bundle\ShippingBundle\Converter\Basic;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;
use Marello\Bundle\ShippingBundle\Converter\ShippingContextToRulesValuesConverterInterface;
use Marello\Bundle\ShippingBundle\ExpressionLanguage\DecoratedProductLineItemFactory;

class ShippingContextToRulesValuesConverter implements ShippingContextToRulesValuesConverterInterface
{
    /**
     * @var DecoratedProductLineItemFactory
     */
    private $decoratedProductLineItemFactory;

    /**
     * @param DecoratedProductLineItemFactory $decoratedProductLineItemFactory
     */
    public function __construct(DecoratedProductLineItemFactory $decoratedProductLineItemFactory)
    {
        $this->decoratedProductLineItemFactory = $decoratedProductLineItemFactory;
    }

    /**
     * @param ShippingContextInterface $context
     * @return array
     */
    public function convert(ShippingContextInterface $context): array
    {
        $lineItems = $context->getLineItems()->toArray();

        return [
            'lineItems' => array_map(function (ShippingLineItemInterface $lineItem) use ($lineItems) {
                return $this->decoratedProductLineItemFactory
                    ->createLineItemWithDecoratedProductByLineItem($lineItems, $lineItem);
            }, $lineItems),
            'billingAddress' => $context->getBillingAddress(),
            'shippingAddress' => $context->getShippingAddress(),
            'shippingOrigin' => $context->getShippingOrigin(),
            'paymentMethod' => $context->getPaymentMethod(),
            'currency' => $context->getCurrency(),
            'subtotal' => $context->getSubtotal(),
            'customer' => $context->getCustomer()
        ];
    }
}
