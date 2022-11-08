<?php

namespace Marello\Bundle\PaymentBundle\Method\Handler;

use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository;
use Marello\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class RulesPaymentMethodDisableHandlerDecorator implements PaymentMethodDisableHandlerInterface
{
    public function __construct(
        private PaymentMethodDisableHandlerInterface $handler,
        private PaymentMethodsConfigsRuleRepository $repository,
        private PaymentMethodProviderInterface $paymentMethodProvider,
        private AclHelper $aclHelper
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function handleMethodDisable($methodId)
    {
        $this->handler->handleMethodDisable($methodId);
        $paymentMethodsConfigsRule = $this->repository->getEnabledRulesByMethod($methodId, $this->aclHelper);
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
