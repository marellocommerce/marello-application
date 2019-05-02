<?php

namespace Marello\Bundle\ShippingBundle\Method\Configuration;

interface MethodLockedShippingMethodConfigurationInterface extends PreConfiguredShippingMethodConfigurationInterface
{
    /**
     * @return bool
     */
    public function isShippingMethodLocked();
}
