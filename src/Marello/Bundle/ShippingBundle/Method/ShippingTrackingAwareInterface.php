<?php

namespace Marello\Bundle\ShippingBundle\Method;

interface ShippingTrackingAwareInterface
{
    /**
     * @param string $number
     * @return string|null
     */
    public function getTrackingLink($number);
}
