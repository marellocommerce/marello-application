<?php

namespace Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Factory;

use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\ShippingLineItemBuilderInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingLineItemInterface;

interface LineItemBuilderByLineItemFactoryInterface
{
    /**
     * @param ShippingLineItemInterface $lineItem
     *
     * @return ShippingLineItemBuilderInterface
     */
    public function createBuilder(ShippingLineItemInterface $lineItem);
}
