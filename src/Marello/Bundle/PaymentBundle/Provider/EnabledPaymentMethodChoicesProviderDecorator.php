<?php

namespace Marello\Bundle\PaymentBundle\Provider;

use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;

class EnabledPaymentMethodChoicesProviderDecorator implements PaymentMethodChoicesProviderInterface
{
    /**
     * @var PaymentMethodProviderInterface
     */
    protected $paymentMethodProvider;

    /**
     * @var PaymentMethodChoicesProviderInterface
     */
    protected $provider;

    /**
     * @param PaymentMethodProviderInterface        $paymentMethodProvider
     * @param PaymentMethodChoicesProviderInterface $provider
     */
    public function __construct(
        PaymentMethodProviderInterface $paymentMethodProvider,
        PaymentMethodChoicesProviderInterface $provider
    ) {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods($translate = false)
    {
        $methods = $this->provider->getMethods($translate);
        $enabledMethods = [];
        foreach ($methods as $methodId => $label) {
            $method = $this->paymentMethodProvider->getPaymentMethod($methodId);
            if (!$method) {
                continue;
            }

            if ($method->isEnabled()) {
                $enabledMethods[$methodId] = $label;
            }
        }

        return $enabledMethods;
    }
}
