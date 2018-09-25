<?php

namespace Marello\Bundle\ShippingBundle\Context\LineItem\Builder\Factory;

use Marello\Bundle\ProductBundle\Model\ProductAwareInterface;
use Marello\Bundle\ShippingBundle\Context\LineItem\Builder\ShippingLineItemBuilderInterface;

interface ShippingLineItemBuilderFactoryInterface
{
    /**
     * @param int                    $quantity
     * @param ProductAwareInterface $productHolder
     *
     * @return ShippingLineItemBuilderInterface
     */
    public function createBuilder(
        $quantity,
        ProductAwareInterface $productHolder
    );
}
