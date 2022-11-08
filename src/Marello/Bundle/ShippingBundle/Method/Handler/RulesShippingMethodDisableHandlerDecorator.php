<?php

namespace Marello\Bundle\ShippingBundle\Method\Handler;

use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class RulesShippingMethodDisableHandlerDecorator implements ShippingMethodDisableHandlerInterface
{
    public function __construct(
        private ShippingMethodDisableHandlerInterface $handler,
        private ShippingMethodsConfigsRuleRepository $repository,
        private ShippingMethodProviderInterface $shippingMethodProvider,
        private AclHelper $aclHelper
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function handleMethodDisable($methodId)
    {
        $this->handler->handleMethodDisable($methodId);
        $shippingMethodsConfigsRule = $this->repository->getEnabledRulesByMethod($methodId, $this->aclHelper);
        foreach ($shippingMethodsConfigsRule as $configRule) {
            if (!$this->configHasEnabledMethod($configRule, $methodId)) {
                $rule = $configRule->getRule();
                $rule->setEnabled(false);
            }
        }
    }

    /**
     * @param ShippingMethodsConfigsRule $configRule
     * @param string                     $disabledMethodId
     *
     * @return bool
     */
    private function configHasEnabledMethod(ShippingMethodsConfigsRule $configRule, $disabledMethodId)
    {
        $methodConfigs = $configRule->getMethodConfigs();
        foreach ($methodConfigs as $methodConfig) {
            $methodId = $methodConfig->getMethod();
            if ($methodId !== $disabledMethodId) {
                $method = $this->shippingMethodProvider->getShippingMethod($methodId);
                if ($method->isEnabled()) {
                    return true;
                }
            }
        }

        return false;
    }
}
