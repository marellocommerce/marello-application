<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator;

use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\Validator\Result\ShippingMethodValidatorResultInterface;

interface ShippingMethodValidatorInterface
{
    /**
     * @param ShippingMethodInterface $shippingMethod
     *
     * @return ShippingMethodValidatorResultInterface
     */
    public function validate(ShippingMethodInterface $shippingMethod);
}
