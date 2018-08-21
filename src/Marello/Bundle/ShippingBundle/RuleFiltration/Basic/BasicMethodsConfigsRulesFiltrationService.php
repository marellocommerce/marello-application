<?php

namespace Marello\Bundle\ShippingBundle\RuleFiltration\Basic;

use Marello\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Converter\ShippingContextToRulesValuesConverterInterface;
use Marello\Bundle\ShippingBundle\RuleFiltration\MethodsConfigsRulesFiltrationServiceInterface;

class BasicMethodsConfigsRulesFiltrationService implements MethodsConfigsRulesFiltrationServiceInterface
{
    /**
     * @var RuleFiltrationServiceInterface
     */
    private $filtrationService;

    /**
     * @var ShippingContextToRulesValuesConverterInterface
     */
    private $shippingContextToRulesValuesConverter;

    /**
     * @param RuleFiltrationServiceInterface                 $filtrationService
     * @param ShippingContextToRulesValuesConverterInterface $converter
     */
    public function __construct(
        RuleFiltrationServiceInterface $filtrationService,
        ShippingContextToRulesValuesConverterInterface $converter
    ) {
        $this->filtrationService = $filtrationService;
        $this->shippingContextToRulesValuesConverter = $converter;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilteredShippingMethodsConfigsRules(
        array $shippingMethodsConfigsRules,
        ShippingContextInterface $context
    ) {
        $arrayContext = $this->shippingContextToRulesValuesConverter->convert($context);

        return $this->filtrationService->getFilteredRuleOwners(
            $shippingMethodsConfigsRules,
            $arrayContext
        );
    }
}
