<?php

namespace Marello\Bundle\ShippingBundle\Condition;

use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Check if shipping method has shipping rules
 * Usage:
 * @marello_shipping_method_has_shipping_rules: method_identifier
 */
class ShippingMethodHasShippingRules extends AbstractShippingMethodHasShippingRules
{
    public function __construct(
        private ShippingMethodsConfigsRuleRepository $repository,
        private AclHelper $aclHelper
    ) {
    }

    /**
     * {@inheritDoc}
     */
    protected function getRulesByMethod($shippingMethodIdentifier)
    {
        return $this->repository->getRulesByMethod($shippingMethodIdentifier, $this->aclHelper);
    }
}
