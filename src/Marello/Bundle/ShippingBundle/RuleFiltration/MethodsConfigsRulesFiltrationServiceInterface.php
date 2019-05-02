<?php

namespace Marello\Bundle\ShippingBundle\RuleFiltration;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;

interface MethodsConfigsRulesFiltrationServiceInterface
{
    /**
     * @param ShippingMethodsConfigsRule[] $shippingMethodsConfigsRules
     * @param ShippingContextInterface     $context
     *
     * @return ShippingMethodsConfigsRule[]
     */
    public function getFilteredShippingMethodsConfigsRules(
        array $shippingMethodsConfigsRules,
        ShippingContextInterface $context
    );
}
