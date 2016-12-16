<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Manager;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerManager;
use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerRegistry;
use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerInterface;

class InventoryBalancerManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var InventoryUpdateContext $inventoryUpdateContext */
    protected $inventoryUpdateContext;

    /** @var InventoryBalancerManager $manager */
    protected $manager;

    /** @var InventoryBalancerRegistry $registry */
    protected $registry;

    /** @var ConfigManager $configManager */
    protected $configManager;

    public function setUp()
    {
        $this->inventoryUpdateContext = $this->getMock(InventoryUpdateContext::class);
        $this->registry = $this
                ->getMockBuilder(InventoryBalancerRegistry::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->configManager = $this
            ->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new InventoryBalancerManager($this->registry, $this->configManager);
    }

    /**
     * Call protected methods for testing
     * @param $obj
     * @param $name
     * @param array $args
     * @return mixed
     */
    protected static function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function testGetSelectedBalancer()
    {
        $this->registry->expects($this->once())
            ->method('getInventoryBalancer')
            ->willReturn(InventoryBalancerInterface::class);

        $this->assertEquals(InventoryBalancerInterface::class, self::callMethod(
            $this->manager,
            'getSelectedInventoryBalancer',
            []
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Inventory Balancer must implement Marello\Bundle\InventoryBundle\Manager\InventoryBalancerInterface
     */
    public function testBalancerNotImplementingInterface()
    {
        $this->registry->expects($this->once())
            ->method('getInventoryBalancer')
            ->willReturn(null);

        $this->manager->balanceInventory($this->inventoryUpdateContext);
    }
}
