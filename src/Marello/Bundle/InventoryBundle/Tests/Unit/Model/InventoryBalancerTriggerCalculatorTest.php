<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Model;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\InventoryBundle\DependencyInjection\Configuration;
use Marello\Bundle\InventoryBundle\Model\VirtualInventoryLevelInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancerTriggerCalculator;

class InventoryBalancerTriggerCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if balancer has reached it's threshold
     */
    public function testBalancerThresholdHasBeenReached()
    {
        $configManager = $this
            ->createMock(ConfigManager::class)
            ->method('get')
            ->with(Configuration::SYSTEM_CONFIG_PATH_THRESHOLD_PERCENTAGE)
            ->willReturn(0.2);

        $calculator = new InventoryBalancerTriggerCalculator($configManager);

        $virtualLevel = $this
            ->createConfiguredMock(
                VirtualInventoryLevelInterface::class,
                [
                    'getInventoryQty' => 20,
                    'getBalancedInventoryQty' => 100
                ]
                );

        $this->assertTrue($calculator->isBalanceThresholdReached($virtualLevel));
    }
}
