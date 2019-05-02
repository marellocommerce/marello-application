<?php

namespace Marello\Bundle\OrderBundle\Model;

interface QuantityAwareInterface
{
    /**
     * @return int
     */
    public function getQuantity();
}
