<?php

namespace Marello\Bundle\PaymentBundle\Method\Provider;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodConfig;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\MethodsConfigsRulesByContextProviderInterface;

class PaymentMethodProvider
{
    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @var MethodsConfigsRulesByContextProviderInterface
     */
    private $paymentMethodsConfigsRulesProvider;

    /**
     * @param PaymentMethodProviderInterface $paymentMethodProvider
     * @param MethodsConfigsRulesByContextProviderInterface $paymentMethodsConfigsRulesProvider
     */
    public function __construct(
        PaymentMethodProviderInterface $paymentMethodProvider,
        MethodsConfigsRulesByContextProviderInterface $paymentMethodsConfigsRulesProvider
    ) {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->paymentMethodsConfigsRulesProvider = $paymentMethodsConfigsRulesProvider;
    }

    /**
     * @param PaymentContextInterface $context
     *
     * @return PaymentMethodInterface[]
     */
    public function getApplicablePaymentMethods(PaymentContextInterface $context)
    {
        $paymentMethodsConfigsRules = $this->paymentMethodsConfigsRulesProvider
            ->getPaymentMethodsConfigsRules($context);

        $paymentMethods = [];

        foreach ($paymentMethodsConfigsRules as $paymentMethodsConfigsRule) {
            $paymentMethods = array_merge(
                $paymentMethods,
                $this->getPaymentMethodsForConfigsRule($paymentMethodsConfigsRule, $context)
            );
        }

        return $paymentMethods;
    }

    /**
     * @param PaymentMethodsConfigsRule $paymentMethodsConfigsRule
     * @param PaymentContextInterface $context
     * @return array
     */
    protected function getPaymentMethodsForConfigsRule(
        PaymentMethodsConfigsRule $paymentMethodsConfigsRule,
        PaymentContextInterface $context
    ) {
        $paymentMethods = [];
        foreach ($paymentMethodsConfigsRule->getMethodConfigs() as $methodConfig) {
            $paymentMethod = $this->getPaymentMethodForConfig($methodConfig, $context);
            if ($paymentMethod) {
                $paymentMethods[$methodConfig->getMethod()] = $paymentMethod;
            }
        }

        return $paymentMethods;
    }

    /**
     * @param PaymentMethodConfig $methodConfig
     * @param PaymentContextInterface $context
     * @return PaymentMethodInterface|null
     */
    protected function getPaymentMethodForConfig(PaymentMethodConfig $methodConfig, PaymentContextInterface $context)
    {
        $identifier = $methodConfig->getMethod();
        if ($this->paymentMethodProvider->hasPaymentMethod($identifier)) {
            $paymentMethod = $this->paymentMethodProvider->getPaymentMethod($identifier);

            if ($paymentMethod->isApplicable($context)) {
                return $paymentMethod;
            }
        }

        return null;
    }
}
