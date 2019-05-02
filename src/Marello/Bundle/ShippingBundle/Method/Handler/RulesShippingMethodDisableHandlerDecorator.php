<?php

namespace Marello\Bundle\ShippingBundle\Method\Handler;

use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;

class RulesShippingMethodDisableHandlerDecorator implements ShippingMethodDisableHandlerInterface
{
    /**
     * @var ShippingMethodDisableHandlerInterface
     */
    private $handler;

    /**
     * @var ShippingMethodsConfigsRuleRepository
     */
    private $repository;

    /**
     * @var ShippingMethodProviderInterface
     */
    private $shippingMethodProvider;

    /**
     * @param ShippingMethodDisableHandlerInterface $handler
     * @param ShippingMethodsConfigsRuleRepository  $repository
     * @param ShippingMethodProviderInterface       $shippingMethodProvider
     */
    public function __construct(
        ShippingMethodDisableHandlerInterface $handler,
        ShippingMethodsConfigsRuleRepository $repository,
        ShippingMethodProviderInterface $shippingMethodProvider
    ) {
        $this->handler = $handler;
        $this->repository = $repository;
        $this->shippingMethodProvider = $shippingMethodProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function handleMethodDisable($methodId)
    {
        $this->handler->handleMethodDisable($methodId);
        $shippingMethodsConfigsRule = $this->repository->getEnabledRulesByMethod($methodId);
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
