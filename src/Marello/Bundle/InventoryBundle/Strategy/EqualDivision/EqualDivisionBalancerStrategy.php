<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumDistance;

use Marello\Bundle\InventoryBundle\Strategy\BalancerStrategyInterface;

class EqualDivisionBalancerStrategy implements BalancerStrategyInterface
{
    const IDENTIFIER = 'equal_division';

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getBalancerResult()
    {

    }
}
