<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\Factory\Common\ParameterBag;

use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\Factory;
// @codingStandardsIgnoreStart
use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\ParameterBag\ParameterBagShippingMethodValidatorResultError;

// @codingStandardsIgnoreEnd

class ParameterBagCommonShippingMethodValidatorResultErrorFactory implements
    Factory\Common\CommonShippingMethodValidatorResultErrorFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createError($message)
    {
        return new ParameterBagShippingMethodValidatorResultError(
            [
                ParameterBagShippingMethodValidatorResultError::FIELD_MESSAGE => $message,
            ]
        );
    }
}
