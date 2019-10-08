<?php

namespace Marello\Bundle\PaymentBundle\Context\Converter\Basic;

use Marello\Bundle\PaymentBundle\Context\Converter\PaymentContextToRulesValueConverterInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItemInterface;
use Marello\Bundle\PaymentBundle\ExpressionLanguage\DecoratedProductLineItemFactory;

/**
 * Converts PaymentContext to an array
 */
class BasicPaymentContextToRulesValueConverter implements PaymentContextToRulesValueConverterInterface
{
    /**
     * @var DecoratedProductLineItemFactory
     */
    protected $decoratedProductLineItemFactory;

    /**
     * @param DecoratedProductLineItemFactory $decoratedProductLineItemFactory
     */
    public function __construct(DecoratedProductLineItemFactory $decoratedProductLineItemFactory)
    {
        $this->decoratedProductLineItemFactory = $decoratedProductLineItemFactory;
    }
    
    /**
     * @inheritDoc
     */
    public function convert(PaymentContextInterface $paymentContext)
    {
        $lineItems = $paymentContext->getLineItems()->toArray();

        return [
            'lineItems' => array_map(function (PaymentLineItemInterface $lineItem) use ($lineItems) {
                return $this->decoratedProductLineItemFactory
                    ->createLineItemWithDecoratedProductByLineItem($lineItems, $lineItem);
            }, $lineItems),
            'billingAddress' => $paymentContext->getBillingAddress(),
            'shippingAddress' => $paymentContext->getShippingAddress(),
            'shippingOrigin' => $paymentContext->getShippingOrigin(),
            'paymentMethod' => $paymentContext->getPaymentMethod(),
            'currency' => $paymentContext->getCurrency(),
            'subtotal' => $paymentContext->getSubtotal(),
            'customer' => $paymentContext->getCustomer(),
            'company' => $paymentContext->getCompany(),
            'total' => $paymentContext->getTotal(),
        ];
    }
}
