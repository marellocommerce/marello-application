<?php

namespace Marello\Bundle\PricingBundle\Subtotal\Provider;

use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;

interface SubtotalProviderInterface
{
    /**
     * Get provider name
     *
     * @return string
     */
    public function getName();

    /**
     * Get entity subtotal
     *
     * @param $entity
     *
     * @return Subtotal[]|Subtotal
     */
    public function getSubtotal($entity);

    /**
     * Check to support provider entity
     *
     * @param $entity
     *
     * @return boolean
     */
    public function isSupported($entity);
}
