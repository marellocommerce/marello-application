<?php

namespace Marello\Bundle\InventoryBundle\Strategy\WFA;

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
    public function addStrategy(WFAStrategyInterface $strategy): self
    {
        $this->strategies[$strategy->getIdentifier()] = $strategy;
        
        return $this;
    }

    /**
     * @param string $identifier
     * @return null|WFAStrategyInterface
     */
    public function getStrategy(string $identifier):? WFAStrategyInterface
    {
        if ($this->hasStrategy($identifier)) {
            return $this->strategies[$identifier];
        }
        return null;
    }

    /**
     * @return WFAStrategyInterface[]
     */
    public function getStrategies(): array
    {
        return $this->strategies;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasStrategy(string $identifier): bool
    {
        if (array_key_exists($identifier, $this->strategies)) {
            return true;
        }
        return false;
    }
}
