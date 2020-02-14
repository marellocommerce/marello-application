<?php

namespace Marello\Bundle\PaymentBundle\Checker;

use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;

interface PaymentRuleEnabledCheckerInterface
{
    /**
     * @param PaymentMethodsConfigsRule $rule
     *
     * @return bool
     */
    public function canBeEnabled(PaymentMethodsConfigsRule $rule);
}
