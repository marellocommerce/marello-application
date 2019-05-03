<?php

namespace Marello\Bundle\OrderBundle\Entity;

interface OrderAwareInterface
{
    /**
     * @return Order
     */
    public function getOrder();
}
