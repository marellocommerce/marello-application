<?php

namespace Marello\Bundle\ShippingBundle\Context\LineItem\Collection\Factory;

use Marello\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;

interface ShippingLineItemCollectionFactoryInterface
{
    /**
     * @param ShippingLineItemInterface[] $shippingLineItems
     *
     * @return ShippingLineItemCollectionInterface
     */
    public function createShippingLineItemCollection(array $shippingLineItems): ShippingLineItemCollectionInterface;
}
