<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Model;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\InventoryBundle\DependencyInjection\Configuration;
use Marello\Bundle\InventoryBundle\Model\VirtualInventoryLevelInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancerTriggerCalculator;

class InventoryBalancerTriggerCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject $configManager */
    protected $configManager;

    /** @var VirtualInventoryLevelInterface|\PHPUnit_Framework_MockObject_MockObject $virtualLevel */
    protected $virtualLevel;

    public function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
    }

    /**
     * Test if balancer has reached it's threshold
     */
    public function testBalancerThresholdHasBeenReached()
    {
        $this->setUpConfigManagerForSystemThreshold();

        $calculator = new InventoryBalancerTriggerCalculator($this->configManager);

        /** @var VirtualInventoryLevelInterface|\PHPUnit_Framework_MockObject_MockObject $virtualLevel */
        $this->setupVirtualLevel(20, 100);

        $this->assertTrue($calculator->isBalanceThresholdReached($this->virtualLevel));
    }

    /**
     * Test if balancer has reached it's threshold
     */
    public function testBalancerThresholdHasNotBeenReached()
    {
        $this->setUpConfigManagerForSystemThreshold();

        $calculator = new InventoryBalancerTriggerCalculator($this->configManager);

        /** @var VirtualInventoryLevelInterface|\PHPUnit_Framework_MockObject_MockObject $virtualLevel */
        $this->setupVirtualLevel(50, 100);

        $this->assertFalse($calculator->isBalanceThresholdReached($this->virtualLevel));
    }

    /**
     * Test calculation of balancer trigger
     */
    public function testBalancerThresholdCalculation()
    {
        $calculator = new InventoryBalancerTriggerCalculator($this->configManager);
        /** @var VirtualInventoryLevelInterface|\PHPUnit_Framework_MockObject_MockObject $virtualLevel */
        $this->setupVirtualLevel(50, 100);

        $this->assertTrue($calculator->calculate($this->virtualLevel, 0.5));
        $this->assertFalse($calculator->calculate($this->virtualLevel, 0.2));
    }

    protected function setupVirtualLevel($inventory, $balancedIventory)
    {
        $this->virtualLevel =
            $this->createConfiguredMock(
                VirtualInventoryLevelInterface::class,
                [
                    'getInventoryQty' => $inventory,
                    'getBalancedInventoryQty' => $balancedIventory
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUpConfigManagerForSystemThreshold()
    {
        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with(Configuration::SYSTEM_CONFIG_PATH_THRESHOLD_PERCENTAGE)
            ->willReturn(0.2);
    }
}
