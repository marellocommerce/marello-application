<?php

namespace Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Basic\Factory;

use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;
use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Basic\BasicShippingLineItemBuilder;
use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Factory\ShippingLineItemBuilderFactoryInterface;

class BasicShippingLineItemBuilderFactory implements ShippingLineItemBuilderFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createBuilder(
        $quantity,
        ProductAwareInterface $productHolder
    ) {
        return new BasicShippingLineItemBuilder($quantity, $productHolder);
    }
}
