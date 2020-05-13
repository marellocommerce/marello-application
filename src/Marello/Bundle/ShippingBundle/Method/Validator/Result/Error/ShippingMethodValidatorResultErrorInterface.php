<?php

namespace Marello\Bundle\ShippingBundle\Method\Validator\Result\Error;

interface ShippingMethodValidatorResultErrorInterface
{
    /**
     * @return string
     */
    public function getMessage();
}
