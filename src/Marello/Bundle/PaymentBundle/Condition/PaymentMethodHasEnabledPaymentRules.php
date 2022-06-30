<?php

namespace Marello\Bundle\PaymentBundle\Condition;

use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Check if payment method has payment rules
 * Usage:
 * @marello_payment_method_has_enabled_payment_rules: method_identifier
 */
class PaymentMethodHasEnabledPaymentRules extends AbstractPaymentMethodHasPaymentRules
{
    public function __construct(
        private PaymentMethodsConfigsRuleRepository $repository,
        private AclHelper $aclHelper
    ) {}

    /**
     * {@inheritDoc}
     */
    protected function getRulesByMethod($paymentMethodIdentifier)
    {
        return $this->repository->getEnabledRulesByMethod($paymentMethodIdentifier, $this->aclHelper);
    }
}
