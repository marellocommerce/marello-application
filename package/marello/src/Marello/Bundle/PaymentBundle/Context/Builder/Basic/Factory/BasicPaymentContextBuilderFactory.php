<?php

namespace Marello\Bundle\PaymentBundle\Context\Builder\Basic\Factory;

use Marello\Bundle\PaymentBundle\Context\Builder\Basic\BasicPaymentContextBuilder;
use Marello\Bundle\PaymentBundle\Context\Builder\Factory\PaymentContextBuilderFactoryInterface;
use Marello\Bundle\PaymentBundle\Context\LineItem\Collection\Factory\PaymentLineItemCollectionFactoryInterface;

class BasicPaymentContextBuilderFactory implements PaymentContextBuilderFactoryInterface
{
    /**
     * @var PaymentLineItemCollectionFactoryInterface
     */
    private $collectionFactory;

    /**
     * @param PaymentLineItemCollectionFactoryInterface $collectionFactory
     */
    public function __construct(PaymentLineItemCollectionFactoryInterface $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createPaymentContextBuilder($sourceEntity, $sourceEntityId)
    {
        return new BasicPaymentContextBuilder(
            $sourceEntity,
            $sourceEntityId,
            $this->collectionFactory
        );
    }
}
