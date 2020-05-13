<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\ParameterBag;

use Marello\Bundle\ShippingBundle\Method\Validator\Result\Error\ShippingMethodValidatorResultErrorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ParameterBagShippingMethodValidatorResultError extends ParameterBag implements
    ShippingMethodValidatorResultErrorInterface
{
    const FIELD_MESSAGE = 'message';

    /**
     * {@inheritDoc}
     */
    public function getMessage()
    {
        return $this->get(self::FIELD_MESSAGE);
    }
}
