<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy;

class WFAStrategiesRegistry
{
    /**
     * @var WFAStrategyInterface[]
     */
    private $strategies = [];

    /**
     * @param WFAStrategyInterface $strategy
     * @return $this
     */
    public function addStrategy(WFAStrategyInterface $strategy)
    {
        $this->strategies[$strategy->getIdentifier()] = $strategy;
        
        return $this;
    }

    /**
     * @param string $identifier
     * @return null|WFAStrategyInterface
     */
    public function getStrategy($identifier)
    {
        if ($this->hasStrategy($identifier)) {
            return $this->strategies[$identifier];
        }
        return null;
    }

    /**
     * @return WFAStrategyInterface[]
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
