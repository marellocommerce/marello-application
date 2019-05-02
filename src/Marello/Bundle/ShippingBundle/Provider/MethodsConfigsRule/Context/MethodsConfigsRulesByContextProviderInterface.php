<?php

namespace Marello\Bundle\ShippingBundle\Provider\MethodsConfigsRule\Context;

use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;

interface MethodsConfigsRulesByContextProviderInterface
{
    /**
     * @param ShippingContextInterface $context
     * @return array|ShippingMethodsConfigsRule[]
     */
    public function getShippingMethodsConfigsRules(ShippingContextInterface $context);
}
