<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Model;

use Marello\Bundle\InventoryBundle\Manager\InventoryItemManager;
use Marello\Bundle\InventoryBundle\Model\BalancedInventory\BalancedInventoryHandler;
use Marello\Bundle\InventoryBundle\Strategy\BalancerStrategiesRegistry;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancer;
use Marello\Bundle\InventoryBundle\Model\BalancedInventoryLevelInterface;

class InventoryBalancerTest extends TestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
    protected $configManager;

    /** @var InventoryBalancer|\PHPUnit\Framework\MockObject\MockObject $inventoryBalancer */
    protected $inventoryBalancer;

    /** @var InventoryItemManager|\PHPUnit\Framework\MockObject\MockObject $inventoryItemManager */
    protected $inventoryItemManager;

    /** @var BalancedInventoryHandler|\PHPUnit\Framework\MockObject\MockObject $balancedInventoryHandler */
    protected $balancedInventoryHandler;

    /** @var BalancerStrategiesRegistry|\PHPUnit\Framework\MockObject\MockObject $balancerRegistry */
    protected $balancerRegistry;

    public function setUp(): void
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->balancerRegistry = $this->createMock(BalancerStrategiesRegistry::class);
        $this->inventoryItemManager = $this->createMock(InventoryItemManager::class);
        $this->balancedInventoryHandler = $this->createMock(BalancedInventoryHandler::class);

        $this->inventoryBalancer = new InventoryBalancer(
            $this->balancerRegistry,
            $this->inventoryItemManager,
            $this->balancedInventoryHandler,
            $this->configManager
        );
    }

    /**
     * Test if balancer is not rebalancing disabled products
     */
    public function testBalancerShouldNotRunWhenProductIsDisabled()
    {
        $productStatus = $this->createConfiguredMock(ProductStatus::class, ['getName' => ProductStatus::DISABLED]);
        $product = $this->createConfiguredMock(Product::class, ['getStatus' => $productStatus]);

        $this->inventoryItemManager->expects(static::never())
            ->method('getInventoryItem')
            ->with($product);
        $this->inventoryBalancer->balanceInventory($product, false, false);
    }
}
