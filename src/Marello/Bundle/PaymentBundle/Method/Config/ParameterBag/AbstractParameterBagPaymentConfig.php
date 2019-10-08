<?php

namespace Marello\Bundle\PaymentBundle\Method\Config\ParameterBag;

use Marello\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractParameterBagPaymentConfig extends ParameterBag implements PaymentConfigInterface
{
    const FIELD_LABEL = 'label';
    const FIELD_PAYMENT_METHOD_IDENTIFIER = 'payment_method_identifier';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->get(self::FIELD_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethodIdentifier()
    {
        return $this->get(self::FIELD_PAYMENT_METHOD_IDENTIFIER);
    }
}
