<?php

namespace Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Doctrine\Factory;

use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Doctrine\DoctrinePaymentLineItemCollection;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Factory\PaymentLineItemCollectionFactoryInterface;
use Marello\Bundle\PaymentBundle\Context\PaymentLineItemInterface;

class DoctrinePaymentLineItemCollectionFactory implements PaymentLineItemCollectionFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createPaymentLineItemCollection(array $paymentLineItems)
    {
        foreach ($paymentLineItems as $paymentLineItem) {
            if (!$paymentLineItem instanceof PaymentLineItemInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Expected: %s', PaymentLineItemInterface::class)
                );
            }
        }

        return new DoctrinePaymentLineItemCollection($paymentLineItems);
    }
}
