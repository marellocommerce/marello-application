<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\Factory\Common;

use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\ShippingMethodValidatorResultErrorInterface;

interface CommonShippingMethodValidatorResultErrorFactoryInterface
{
    /**
     * @param string $message
     *
     * @return ShippingMethodValidatorResultErrorInterface
     */
    public function createError($message);
}
