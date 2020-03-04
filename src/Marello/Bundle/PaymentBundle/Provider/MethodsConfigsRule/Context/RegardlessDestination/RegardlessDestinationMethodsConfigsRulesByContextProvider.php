<?php

namespace Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination;

use Marello\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodsConfigsRuleRepository;
use Marello\Bundle\PaymentBundle\Provider\MethodsConfigsRule\Context\MethodsConfigsRulesByContextProviderInterface;
use Marello\Bundle\PaymentBundle\RuleFiltration\MethodsConfigsRulesFiltrationServiceInterface;

class RegardlessDestinationMethodsConfigsRulesByContextProvider implements MethodsConfigsRulesByContextProviderInterface
{
    /**
     * @var MethodsConfigsRulesFiltrationServiceInterface
     */
    private $filtrationService;

    /**
     * @var PaymentMethodsConfigsRuleRepository
     */
    private $repository;

    /**
     * @param MethodsConfigsRulesFiltrationServiceInterface $filtrationService
     * @param PaymentMethodsConfigsRuleRepository          $repository
     */
    public function __construct(
        MethodsConfigsRulesFiltrationServiceInterface $filtrationService,
        PaymentMethodsConfigsRuleRepository $repository
    ) {
        $this->filtrationService = $filtrationService;
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethodsConfigsRules(PaymentContextInterface $context)
    {
        if ($context->getBillingAddress()) {
            $methodsConfigsRules = $this->repository->getByDestinationAndCurrency(
                $context->getBillingAddress(),
                $context->getCurrency()
            );
        } else {
            $methodsConfigsRules = $this->repository->getByCurrency(
                $context->getCurrency()
            );
        }

        return $this->filtrationService->getFilteredPaymentMethodsConfigsRules(
            $methodsConfigsRules,
            $context
        );
    }
}
