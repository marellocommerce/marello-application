<?php

namespace Marello\Bundle\ShippingBundle\Context\Builder\Basic\Factory;

use Marello\Bundle\ShippingBundle\Context\Builder\Basic\BasicShippingContextBuilder;
use Marello\Bundle\ShippingBundle\Context\Builder\Factory\ShippingContextBuilderFactoryInterface;

class BasicShippingContextBuilderFactory implements ShippingContextBuilderFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createShippingContextBuilder($sourceEntity, $sourceEntityId)
    {
        return new BasicShippingContextBuilder($sourceEntity, $sourceEntityId);
    }
}
