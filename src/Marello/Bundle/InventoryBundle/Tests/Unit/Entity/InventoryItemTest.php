<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Entity\StockLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;

class InventoryItemTest extends WebTestCase
{
    /** @var InventoryManager $manager */
    protected $manager;

    public function setUp()
    {
        $this->initClient();

        $this->manager = $this->client->getContainer()->get('marello_inventory.manager.inventory_manager');
    }

    /** @test */
    public function testInventorySet()
    {
        $inventoryItem = new InventoryItem(new Warehouse(), new Product());

        $this->assertEquals(0, $inventoryItem->getStock());
        $this->assertEquals(0, $inventoryItem->getAllocatedStock());
        $this->assertEquals(0, $inventoryItem->getVirtualStock());
        $this->assertNull($inventoryItem->getCurrentLevel());

        /** @var InventoryUpdateContext $context */
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $inventoryItem,
            10,
            20,
            'manual'
        );
        $this->manager->updateInventoryItems($context);

        $this->assertEquals(10, $inventoryItem->getStock());
        $this->assertEquals(20, $inventoryItem->getAllocatedStock());
        $this->assertEquals(-10, $inventoryItem->getVirtualStock());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getCurrentLevel());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getLevels()->first());
    }

    /** @test */
    public function testInventoryAdjust()
    {
        $inventoryItem = new InventoryItem(new Warehouse(), new Product());

        $this->assertEquals(0, $inventoryItem->getStock());
        $this->assertEquals(0, $inventoryItem->getAllocatedStock());
        $this->assertEquals(0, $inventoryItem->getVirtualStock());
        $this->assertNull($inventoryItem->getCurrentLevel());

        /** @var InventoryUpdateContext $context */
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $inventoryItem,
            10,
            20,
            'manual'
        );
        $this->manager->updateInventoryItems($context);

        $this->assertEquals(10, $inventoryItem->getStock());
        $this->assertEquals(20, $inventoryItem->getAllocatedStock());
        $this->assertEquals(-10, $inventoryItem->getVirtualStock());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getCurrentLevel());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getLevels()->first());

        /** @var InventoryUpdateContext $context */
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $inventoryItem,
            -5,
            -10,
            'manual'
        );
        $this->manager->updateInventoryItems($context);

        $this->assertEquals(5, $inventoryItem->getStock());
        $this->assertEquals(10, $inventoryItem->getAllocatedStock());
        $this->assertEquals(-5, $inventoryItem->getVirtualStock());
    }
}
