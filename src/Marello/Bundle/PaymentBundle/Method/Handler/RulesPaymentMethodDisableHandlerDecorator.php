<?php

namespace Marello\Bundle\PaymentBundle\Method\Handler;

use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;

class RulesPaymentMethodDisableHandlerDecorator implements PaymentMethodDisableHandlerInterface
{
    /**
     * @var PaymentMethodDisableHandlerInterface
     */
    private $handler;

    /**
     * @var PaymentMethodsConfigsRuleRepository
     */
    private $repository;

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @param PaymentMethodDisableHandlerInterface $handler
     * @param PaymentMethodsConfigsRuleRepository  $repository
     * @param PaymentMethodProviderInterface       $paymentMethodProvider
     */
    public function __construct(
        PaymentMethodDisableHandlerInterface $handler,
        PaymentMethodsConfigsRuleRepository $repository,
        PaymentMethodProviderInterface $paymentMethodProvider
    ) {
        $this->handler = $handler;
        $this->repository = $repository;
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function handleMethodDisable($methodId)
    {
        $this->handler->handleMethodDisable($methodId);
        $paymentMethodsConfigsRule = $this->repository->getEnabledRulesByMethod($methodId);
        foreach ($paymentMethodsConfigsRule as $configRule) {
            if (!$this->configHasEnabledMethod($configRule, $methodId)) {
                $rule = $configRule->getRule();
                $rule->setEnabled(false);
            }
        }
    }

    /**
     * @param PaymentMethodsConfigsRule $configRule
     * @param string                     $disabledMethodId
     *
     * @return bool
     */
    private function configHasEnabledMethod(PaymentMethodsConfigsRule $configRule, $disabledMethodId)
    {
        $methodConfigs = $configRule->getMethodConfigs();
        foreach ($methodConfigs as $methodConfig) {
            $methodId = $methodConfig->getMethod();
            if ($methodId !== $disabledMethodId) {
                $method = $this->paymentMethodProvider->getPaymentMethod($methodId);
                if ($method->isEnabled()) {
                    return true;
                }
            }
        }

        return false;
    }
}
