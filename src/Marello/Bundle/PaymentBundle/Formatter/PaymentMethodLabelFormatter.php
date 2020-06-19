<?php

namespace Marello\Bundle\PaymentBundle\Formatter;

use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;

class PaymentMethodLabelFormatter
{
    /**
     * @var PaymentMethodProviderInterface
     */
    protected $paymentMethodProvider;

    /**
     * @param PaymentMethodProviderInterface $paymentMethodProvider
     */
    public function __construct(PaymentMethodProviderInterface $paymentMethodProvider)
    {
        $this->paymentMethodProvider = $paymentMethodProvider;
    }


    /**
     * @param string $paymentMethodName
     * @return string
     */
    public function formatPaymentMethodLabel($paymentMethodName)
    {
        $paymentMethod = $this->paymentMethodProvider->getPaymentMethod($paymentMethodName);

        return $paymentMethod->getLabel();
    }
}
