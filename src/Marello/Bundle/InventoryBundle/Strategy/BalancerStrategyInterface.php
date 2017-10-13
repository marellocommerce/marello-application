<?php

namespace Marello\Bundle\InventoryBundle\Strategy;

interface BalancerStrategyInterface
{
    /**
     * @return string|int
     */
    public function getIdentifier();

    public function getBalancerResult();
}
