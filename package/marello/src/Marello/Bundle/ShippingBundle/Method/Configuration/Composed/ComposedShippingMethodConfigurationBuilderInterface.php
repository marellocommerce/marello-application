<?php

namespace Marello\Bundle\ShippingBundle\Method\Configuration\Composed;

use Marello\Bundle\ShippingBundle\Method\Configuration\AllowUnlistedShippingMethodConfigurationInterface;
use Marello\Bundle\ShippingBundle\Method\Configuration\MethodLockedShippingMethodConfigurationInterface;
use Marello\Bundle\ShippingBundle\Method\Configuration\OverriddenCostShippingMethodConfigurationInterface;
use Marello\Bundle\ShippingBundle\Method\Configuration\PreConfiguredShippingMethodConfigurationInterface;

interface ComposedShippingMethodConfigurationBuilderInterface
{
    /**
     * @return ComposedShippingMethodConfigurationInterface
     */
    public function getResult();

    /**
     * @param PreConfiguredShippingMethodConfigurationInterface $preConfiguredShippingMethodConfiguration
     *
     * @return self
     */
    public function buildShippingMethod(
        PreConfiguredShippingMethodConfigurationInterface $preConfiguredShippingMethodConfiguration
    );

    /**
     * @param PreConfiguredShippingMethodConfigurationInterface $preConfiguredShippingMethodConfiguration
     *
     * @return self
     */
    public function buildShippingMethodType(
        PreConfiguredShippingMethodConfigurationInterface $preConfiguredShippingMethodConfiguration
    );

    /**
     * @param PreConfiguredShippingMethodConfigurationInterface $preConfiguredShippingMethodConfiguration
     *
     * @return self
     */
    public function buildShippingCost(
        PreConfiguredShippingMethodConfigurationInterface $preConfiguredShippingMethodConfiguration
    );

    /**
     * @param OverriddenCostShippingMethodConfigurationInterface $overriddenCostShippingMethodConfiguration
     *
     * @return self
     */
    public function buildIsOverriddenCost(
        OverriddenCostShippingMethodConfigurationInterface $overriddenCostShippingMethodConfiguration
    );

    /**
     * @param MethodLockedShippingMethodConfigurationInterface $methodLockedShippingMethodConfiguration
     *
     * @return self
     */
    public function buildIsShippingMethodLocked(
        MethodLockedShippingMethodConfigurationInterface $methodLockedShippingMethodConfiguration
    );

    /**
     * @param AllowUnlistedShippingMethodConfigurationInterface $allowUnlistedShippingMethodConfiguration
     *
     * @return self
     */
    public function buildIsAllowUnlistedShippingMethod(
        AllowUnlistedShippingMethodConfigurationInterface $allowUnlistedShippingMethodConfiguration
    );
}
