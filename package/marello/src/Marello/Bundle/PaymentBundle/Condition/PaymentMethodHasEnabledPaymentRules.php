<?php

namespace Marello\Bundle\PaymentBundle\Condition;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository;

/**
 * Check if payment method has payment rules
 * Usage:
 * @marello_payment_method_has_enabled_payment_rules: method_identifier
 */
class PaymentMethodHasEnabledPaymentRules extends AbstractPaymentMethodHasPaymentRules
{
    /**
     * @var PaymentMethodsConfigsRuleRepository
     */
    private $repository;

    /**
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     */
    protected function getRulesByMethod($paymentMethodIdentifier)
    {
        return $this->repository->getEnabledRulesByMethod($paymentMethodIdentifier);
    }
}
