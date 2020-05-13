<?php

namespace Marello\Bundle\PaymentBundle\Context\Converter;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;

interface PaymentContextToRulesValueConverterInterface
{
    /**
     * @param PaymentContextInterface $paymentContext
     *
     * @return array
     */
    public function convert(PaymentContextInterface $paymentContext);
}
