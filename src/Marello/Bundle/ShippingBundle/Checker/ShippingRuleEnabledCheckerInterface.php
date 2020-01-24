<?php

namespace Marello\Bundle\ShippingBundle\Checker;

use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;

interface ShippingRuleEnabledCheckerInterface
{
    /**
     * @param ShippingMethodsConfigsRule $rule
     *
     * @return bool
     */
    public function canBeEnabled(ShippingMethodsConfigsRule $rule);
}
