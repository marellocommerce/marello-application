<?php

namespace Marello\Bundle\InventoryBundle\Model\InventoryBalancer;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\InventoryBundle\DependencyInjection\Configuration;
use Marello\Bundle\InventoryBundle\Model\BalancedInventoryLevelInterface;

class InventoryBalancerTriggerCalculator
{
    /** @var ConfigManager $configManager */
    private $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * Check whether the balance threshold has been reached
     * @param BalancedInventoryLevelInterface $balancedInventoryLevel
     * @return bool
     */
    public function isBalanceThresholdReached(BalancedInventoryLevelInterface $balancedInventoryLevel)
    {
        $balanceThreshold = $this->getBalanceTriggerThreshold();
        return $this->calculate($balancedInventoryLevel, $balanceThreshold);
    }

    /**
     * Calculate the percentage the inventory currently is and compare it to systems threshold
     * @param BalancedInventoryLevelInterface $balancedInventoryLevel
     * @param float $balanceThreshold
     * @return bool
     */
    public function calculate(BalancedInventoryLevelInterface $balancedInventoryLevel, $balanceThreshold)
    {
        $currentInventoryQty = $balancedInventoryLevel->getInventoryQty();
        $balancedInventoryQty = $balancedInventoryLevel->getBalancedInventoryQty();
        if ($balancedInventoryQty === 0) {
            return false;
        }

        // percentages are stored in decimal numbers (i.e. 20% is 0.2)
        $percentage = ($currentInventoryQty / $balancedInventoryQty);

        return ((float)$percentage <= (float) $balanceThreshold);
    }

    /**
     * Get inventory balance threshold percentage from config
     * @return float
     */
    protected function getBalanceTriggerThreshold()
    {
        return $this->configManager->get(Configuration::SYSTEM_CONFIG_PATH_THRESHOLD_PERCENTAGE);
    }
}
