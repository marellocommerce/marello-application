<?php

namespace Marello\Bundle\PricingBundle\Subtotal\Provider;

use Marello\Bundle\PricingBundle\Subtotal\Model\Subtotal;

interface TotalAwareSubtotalProviderInterface extends SubtotalProviderInterface
{
    /**
     * Get entity total
     *
     * @param $entity
     *
     * @return Subtotal[]|Subtotal
     */
    public function getTotal($entity, iterable $subtotals = []);
}
