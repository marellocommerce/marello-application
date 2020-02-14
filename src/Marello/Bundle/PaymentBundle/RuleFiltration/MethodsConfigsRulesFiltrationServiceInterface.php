<?php

namespace Marello\Bundle\PaymentBundle\RuleFiltration;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;

interface MethodsConfigsRulesFiltrationServiceInterface
{
    /**
     * @param PaymentMethodsConfigsRule[] $paymentMethodsConfigsRules
     * @param PaymentContextInterface     $context
     *
     * @return PaymentMethodsConfigsRule[]
     */
    public function getFilteredPaymentMethodsConfigsRules(
        array $paymentMethodsConfigsRules,
        PaymentContextInterface $context
    );
}
