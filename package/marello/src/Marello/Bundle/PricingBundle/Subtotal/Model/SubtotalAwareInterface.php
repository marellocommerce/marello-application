<?php
namespace Marello\Bundle\PricingBundle\Subtotal\Model;

/**
 * Interface for entities with subtotal.
 */
interface SubtotalAwareInterface
{
    /**
     * @return float
     */
    public function getSubtotal();
}
