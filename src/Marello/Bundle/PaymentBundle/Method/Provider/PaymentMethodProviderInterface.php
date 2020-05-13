<?php

namespace Marello\Bundle\PaymentBundle\Method\Provider;

use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;

interface PaymentMethodProviderInterface
{
    /**
     * @return PaymentMethodInterface[]
     */
    public function getPaymentMethods();

    /**
     * @param string $identifier
     * @return PaymentMethodInterface
     */
    public function getPaymentMethod($identifier);

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasPaymentMethod($identifier);
}
