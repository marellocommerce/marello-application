<?php

namespace Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\Basic;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;
use Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\MethodsConfigsRulesByContextProviderInterface;
use Marello\Bundle\ShippingBundle\RuleFiltration\MethodsConfigsRulesFiltrationServiceInterface;

class BasicMethodsConfigsRulesByContextProvider implements MethodsConfigsRulesByContextProviderInterface
{
    /**
     * @var MethodsConfigsRulesFiltrationServiceInterface
     */
    private $filtrationService;

    /**
     * @var ShippingMethodsConfigsRuleRepository
     */
    private $repository;

    /**
     * @param MethodsConfigsRulesFiltrationServiceInterface $filtrationService
     * @param ShippingMethodsConfigsRuleRepository          $repository
     */
    public function __construct(
        MethodsConfigsRulesFiltrationServiceInterface $filtrationService,
        ShippingMethodsConfigsRuleRepository $repository
    ) {
        $this->filtrationService = $filtrationService;
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingMethodsConfigsRules(ShippingContextInterface $context)
    {
        if ($context->getShippingAddress()) {
            $methodsConfigsRules = $this->repository->getByDestinationAndCurrency(
                $context->getShippingAddress(),
                $context->getCurrency()
            );
        } else {
            $methodsConfigsRules = $this->repository->getByCurrencyWithoutDestination(
                $context->getCurrency()
            );
        }

        return $this->filtrationService->getFilteredShippingMethodsConfigsRules(
            $methodsConfigsRules,
            $context
        );
    }
}
