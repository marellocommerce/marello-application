<?php

namespace Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;

interface MethodsConfigsRulesByContextProviderInterface
{
    /**
     * @param PaymentContextInterface $context
     * @return array|PaymentMethodsConfigsRule[]
     */
    public function getPaymentMethodsConfigsRules(PaymentContextInterface $context);
}
