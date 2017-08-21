<?php

namespace Marello\Bundle\ProductBundle\Model;

interface QuantityAwareInterface
{
    /**
     * @return int
     */
    public function getQuantity();
}
