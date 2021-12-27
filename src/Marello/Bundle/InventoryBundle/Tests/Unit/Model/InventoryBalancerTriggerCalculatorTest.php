<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Model;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\InventoryBundle\DependencyInjection\Configuration;
use Marello\Bundle\InventoryBundle\Model\BalancedInventoryLevelInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancerTriggerCalculator;

class InventoryBalancerTriggerCalculatorTest extends TestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
    protected $configManager;

    /** @var BalancedInventoryLevelInterface|\PHPUnit\Framework\MockObject\MockObject $virtualLevel */
    protected $balancedInventoryLevel;

    public function setUp(): void
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

        /** @var BalancedInventoryLevelInterface|\PHPUnit\Framework\MockObject\MockObject $balancedInventoryLevel */
        $this->setupBalancedInventoryLevel(20, 100);

        $this->assertTrue($calculator->isBalanceThresholdReached($this->balancedInventoryLevel));
    }

    /**
     * Test if balancer has reached it's threshold
     */
    public function testBalancerThresholdHasNotBeenReached()
    {
        $this->setUpConfigManagerForSystemThreshold();

        $calculator = new InventoryBalancerTriggerCalculator($this->configManager);

        /** @var BalancedInventoryLevelInterface|\PHPUnit\Framework\MockObject\MockObject $balancedInventoryLevel */
        $this->setupBalancedInventoryLevel(50, 100);

        $this->assertFalse($calculator->isBalanceThresholdReached($this->balancedInventoryLevel));
    }

    /**
     * Test calculation of balancer trigger
     */
    public function testBalancerThresholdCalculation()
    {
        $calculator = new InventoryBalancerTriggerCalculator($this->configManager);
        /** @var BalancedInventoryLevelInterface|\PHPUnit\Framework\MockObject\MockObject $balancedInventoryLevel */
        $this->setupBalancedInventoryLevel(50, 100);

        $this->assertTrue($calculator->calculate($this->balancedInventoryLevel, 0.5));
        $this->assertFalse($calculator->calculate($this->balancedInventoryLevel, 0.2));
    }

    protected function setupBalancedInventoryLevel($inventory, $balancedIventory)
    {
        $this->balancedInventoryLevel =
            $this->createConfiguredMock(
                BalancedInventoryLevelInterface::class,
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
