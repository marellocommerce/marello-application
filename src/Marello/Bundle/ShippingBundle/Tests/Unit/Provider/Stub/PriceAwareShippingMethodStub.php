<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Provider\Stub;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Method\PricesAwareShippingMethodInterface;

class PriceAwareShippingMethodStub extends ShippingMethodStub implements PricesAwareShippingMethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function calculatePrices(ShippingContextInterface $context, array $methodOptions, array $optionsByTypes)
    {
        return array_combine(array_keys($optionsByTypes), array_map(function ($options) {
            return $options['aware_price'];
        }, $optionsByTypes));
    }
}
