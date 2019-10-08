<?php

namespace Marello\Bundle\PaymentBundle\Checker;

use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;

class PaymentMethodEnabledByIdentifierChecker implements PaymentMethodEnabledByIdentifierCheckerInterface
{
    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @param PaymentMethodProviderInterface $paymentMethodProvider
     */
    public function __construct(PaymentMethodProviderInterface $paymentMethodProvider)
    {
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled($identifier)
    {
        return $this->paymentMethodProvider->getPaymentMethod($identifier) !== null ?
            $this->paymentMethodProvider->getPaymentMethod($identifier)->isEnabled() :
            false;
    }
}
