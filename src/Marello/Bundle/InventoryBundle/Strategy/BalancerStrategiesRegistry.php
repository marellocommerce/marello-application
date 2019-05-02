<?php

namespace Marello\Bundle\InventoryBundle\Strategy;

class BalancerStrategiesRegistry
{
    /**
     * @var BalancerStrategyInterface[]
     */
    private $strategies = [];

    /**
     * @param BalancerStrategyInterface $strategy
     * @return $this
     */
    public function addStrategy(BalancerStrategyInterface $strategy)
    {
        $this->strategies[$strategy->getIdentifier()] = $strategy;
        
        return $this;
    }

    /**
     * @param string $identifier
     * @return null|BalancerStrategyInterface
     */
    public function getStrategy($identifier)
    {
        if ($this->hasStrategy($identifier)) {
            return $this->strategies[$identifier];
        }
        return null;
    }

    /**
     * @return BalancerStrategyInterface[]
     */
    public function getStrategies()
    {
        return $this->strategies;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasStrategy($identifier)
    {
        if (array_key_exists($identifier, $this->strategies)) {
            return true;
        }
        return false;
    }
}
