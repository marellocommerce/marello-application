<?php

namespace Marello\Bundle\ShippingBundle\Method;

interface TrackingAwareShippingMethodsProviderInterface
{
    /**
     * @return ShippingMethodInterface[]
     */
    public function getTrackingAwareShippingMethods();
}
