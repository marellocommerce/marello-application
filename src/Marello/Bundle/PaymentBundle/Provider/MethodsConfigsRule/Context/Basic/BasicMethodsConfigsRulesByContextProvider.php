<?php

namespace Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\Basic;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository;
use Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\MethodsConfigsRulesByContextProviderInterface;
use Marello\Bundle\PaymentBundle\RuleFiltration\MethodsConfigsRulesFiltrationServiceInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class BasicMethodsConfigsRulesByContextProvider implements MethodsConfigsRulesByContextProviderInterface
{
    public function __construct(
        private MethodsConfigsRulesFiltrationServiceInterface $filtrationService,
        private PaymentMethodsConfigsRuleRepository $repository,
        private AclHelper $aclHelper
    ) {}

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethodsConfigsRules(PaymentContextInterface $context)
    {
        if ($context->getBillingAddress()) {
            $methodsConfigsRules = $this->repository->getByDestinationAndCurrency(
                $context->getBillingAddress(),
                $context->getCurrency(),
                $this->aclHelper
            );
        } else {
            $methodsConfigsRules = $this->repository->getByCurrencyWithoutDestination(
                $context->getCurrency(),
                $this->aclHelper
            );
        }

        return $this->filtrationService->getFilteredPaymentMethodsConfigsRules(
            $methodsConfigsRules,
            $context
        );
    }
}
