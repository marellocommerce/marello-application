<?php

namespace Marello\Bundle\PaymentBundle\Provider;

use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BasicPaymentMethodChoicesProvider implements PaymentMethodChoicesProviderInterface
{
    /**
     * @var PaymentMethodProviderInterface
     */
    protected $paymentMethodProvider;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param PaymentMethodProviderInterface $paymentMethodProvider
     * @param TranslatorInterface             $translator
     */
    public function __construct(
        PaymentMethodProviderInterface $paymentMethodProvider,
        TranslatorInterface $translator
    ) {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods($translate = false)
    {
        return array_reduce(
            $this->paymentMethodProvider->getPaymentMethods(),
            function (array $result, PaymentMethodInterface $method) use ($translate) {
                $label = $method->getLabel();
                if ($translate) {
                    $label = $this->translator->trans($label);
                }
                $result[$method->getIdentifier()] = $label;

                return $result;
            },
            []
        );
    }
}
