<?php

namespace Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\RegardlessDestination;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;
use Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context\MethodsConfigsRulesByContextProviderInterface;
use Marello\Bundle\ShippingBundle\RuleFiltration\MethodsConfigsRulesFiltrationServiceInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class RegardlessDestinationMethodsConfigsRulesByContextProvider implements MethodsConfigsRulesByContextProviderInterface
{
    public function __construct(
        private MethodsConfigsRulesFiltrationServiceInterface $filtrationService,
        private ShippingMethodsConfigsRuleRepository $repository,
        private AclHelper $aclHelper
    ) {}

    /**
     * {@inheritDoc}
     */
    public function getShippingMethodsConfigsRules(ShippingContextInterface $context)
    {
        if ($context->getShippingAddress()) {
            $methodsConfigsRules = $this->repository->getByDestinationAndCurrency(
                $context->getShippingAddress(),
                $context->getCurrency(),
                $this->aclHelper
            );
        } else {
            $methodsConfigsRules = $this->repository->getByCurrency(
                $context->getCurrency(),
                $this->aclHelper
            );
        }

        return $this->filtrationService->getFilteredShippingMethodsConfigsRules(
            $methodsConfigsRules,
            $context
        );
    }
}
