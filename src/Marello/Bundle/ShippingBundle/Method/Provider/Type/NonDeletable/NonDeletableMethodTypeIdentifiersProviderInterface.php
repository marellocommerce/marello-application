<?php

namespace Marello\Bundle\ShippingBundle\Method\Provider\Type\NonDeletable;

use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;

interface NonDeletableMethodTypeIdentifiersProviderInterface
{
    /**
     * @param ShippingMethodInterface $shippingMethod
     *
     * @return string[]
     */
    public function getMethodTypeIdentifiers(ShippingMethodInterface $shippingMethod);
}
