<?php

namespace Marello\Bundle\InventoryBundle\Model\InventoryBalancer;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\InventoryBundle\DependencyInjection\Configuration;

class InventoryBalancerTriggerCalculator
{
    /** @var ConfigManager $configManager */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * Check whether the balance threshold has been reached
     * @param VirtualInventoryLevel $virtualInventoryLevel
     * @return bool
     */
    public function isBalanceThresholdReached(VirtualInventoryLevel $virtualInventoryLevel)
    {
        $balanceThreshold = $this->getBalanceTriggerThreshold();
        return $this->calculate($virtualInventoryLevel, $balanceThreshold);
    }

    /**
     * Calculate the percentage the inventory currently is and compare it to systems threshold
     * @param VirtualInventoryLevel $virtualInventoryLevel
     * @param float $balanceThreshold
     * @return bool
     */
    protected function calculate(VirtualInventoryLevel $virtualInventoryLevel, $balanceThreshold)
    {
        $currentInventoryQty = $virtualInventoryLevel->getInventory();
        $originalInventoryQty = $virtualInventoryLevel->getOrgInventory();
        // percentages are stored in decimal numbers (i.e. 20% is 0.2)
        $percentage = ($currentInventoryQty / $originalInventoryQty);

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
