<?php

namespace Marello\Bundle\ShippingBundle\Method\Configuration;

interface AllowUnlistedShippingMethodConfigurationInterface extends PreConfiguredShippingMethodConfigurationInterface
{
    /**
     * @return bool
     */
    public function isAllowUnlistedShippingMethod();
}
