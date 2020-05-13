<?php

namespace Marello\Bundle\PaymentBundle\Checker;

use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;

class PaymentRuleEnabledChecker implements PaymentRuleEnabledCheckerInterface
{
    /**
     * @var PaymentMethodEnabledByIdentifierCheckerInterface
     */
    private $methodEnabledChecker;

    /**
     * @param PaymentMethodEnabledByIdentifierCheckerInterface $methodEnabledChecker
     */
    public function __construct(PaymentMethodEnabledByIdentifierCheckerInterface $methodEnabledChecker)
    {
        $this->methodEnabledChecker = $methodEnabledChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeEnabled(PaymentMethodsConfigsRule $rule)
    {
        foreach ($rule->getMethodConfigs() as $config) {
            if ($this->methodEnabledChecker->isEnabled($config->getMethod())) {
                return true;
            }
        }

        return false;
    }
}
