<?php

namespace Marello\Bundle\ShippingBundle\Condition;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodsConfigsRuleRepository;

/**
 * Check if shipping method has shipping rules
 * Usage:
 * @marello_shipping_method_has_enabled_shipping_rules: method_identifier
 */
class ShippingMethodHasEnabledShippingRules extends AbstractShippingMethodHasShippingRules
{
    /**
     * @var ShippingMethodsConfigsRuleRepository
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
    protected function getRulesByMethod($shippingMethodIdentifier)
    {
        return $this->repository->getEnabledRulesByMethod($shippingMethodIdentifier);
    }
}
