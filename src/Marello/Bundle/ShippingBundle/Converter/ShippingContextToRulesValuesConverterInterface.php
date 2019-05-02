<?php

namespace Marello\Bundle\ShippingBundle\Converter;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;

interface ShippingContextToRulesValuesConverterInterface
{
    /**
     * @param ShippingContextInterface $context
     * @return array
     */
    public function convert(ShippingContextInterface $context);
}
