<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy;

class ReplenishmentStrategiesRegistry
{
    /**
     * @var ReplenishmentStrategyInterface[]
     */
    private $strategies = [];

    /**
     * @param ReplenishmentStrategyInterface $strategy
     * @return $this
     */
    public function addStrategy(ReplenishmentStrategyInterface $strategy)
    {
        $this->strategies[$strategy->getIdentifier()] = $strategy;

        return $this;
    }

    /**
     * @param string $identifier
     * @return null|ReplenishmentStrategyInterface
     */
    public function getStrategy($identifier)
    {
        if ($this->hasStrategy($identifier)) {
            return $this->strategies[$identifier];
        }
        return null;
    }

    /**
     * @return ReplenishmentStrategyInterface[]
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
