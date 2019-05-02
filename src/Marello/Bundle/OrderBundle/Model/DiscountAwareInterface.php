<?php

namespace Marello\Bundle\OrderBundle\Model;

interface DiscountAwareInterface
{
    /**
     * @return float
     */
    public function getDiscountAmount();

    /**
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount);
}
