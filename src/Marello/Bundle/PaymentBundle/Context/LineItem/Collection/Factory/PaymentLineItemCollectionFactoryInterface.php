<?php

namespace Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Factory;

use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\PaymentLineItemCollectionInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItemInterface;

interface PaymentLineItemCollectionFactoryInterface
{
    /**
     * @param array|PaymentLineItemInterface[] $paymentLineItems
     *
     * @return PaymentLineItemCollectionInterface
     */
    public function createPaymentLineItemCollection(array $paymentLineItems);
}
