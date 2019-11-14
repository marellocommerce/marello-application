<?php

namespace Marello\Bundle\PaymentBundle\Context\LineItem\Builder\Basic\Factory;

use Marello\Bundle\PaymentBundle\Context\LineItem\Builder\Basic\BasicPaymentLineItemBuilder;
use Marello\Bundle\PaymentBundle\Context\LineItem\Builder\Factory\PaymentLineItemBuilderFactoryInterface;
use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;

class BasicPaymentLineItemBuilderFactory implements PaymentLineItemBuilderFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createBuilder(
        $quantity,
        ProductAwareInterface $productHolder
    ) {
        return new BasicPaymentLineItemBuilder($quantity, $productHolder);
    }
}
