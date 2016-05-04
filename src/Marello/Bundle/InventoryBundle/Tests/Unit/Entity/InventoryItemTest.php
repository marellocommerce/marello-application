<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\StockLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class InventoryItemTest extends WebTestCase
{
    /** @test */
    public function testInventorySet()
    {
        $product   = $this->prophesize(Product::class);
        $warehouse = $this->prophesize(Warehouse::class);

        $inventoryItem = new InventoryItem($product->reveal(), $warehouse->reveal());

        $this->assertEquals(0, $inventoryItem->getStock());
        $this->assertEquals(0, $inventoryItem->getAllocatedStock());
        $this->assertEquals(0, $inventoryItem->getVirtualStock());
        $this->assertNull($inventoryItem->getCurrentLevel());

        $inventoryItem->setStockLevels('manual', 10, 20);

        $this->assertEquals(10, $inventoryItem->getStock());
        $this->assertEquals(20, $inventoryItem->getAllocatedStock());
        $this->assertEquals(-10, $inventoryItem->getVirtualStock());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getCurrentLevel());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getLevels()->first());
    }

    /** @test */
    public function testInventoryAdjust()
    {
        $product   = $this->prophesize(Product::class);
        $warehouse = $this->prophesize(Warehouse::class);

        $inventoryItem = new InventoryItem($product->reveal(), $warehouse->reveal());

        $this->assertEquals(0, $inventoryItem->getStock());
        $this->assertEquals(0, $inventoryItem->getAllocatedStock());
        $this->assertEquals(0, $inventoryItem->getVirtualStock());
        $this->assertNull($inventoryItem->getCurrentLevel());

        $inventoryItem->adjustStockLevels('manual', 10, 20);

        $this->assertEquals(10, $inventoryItem->getStock());
        $this->assertEquals(20, $inventoryItem->getAllocatedStock());
        $this->assertEquals(-10, $inventoryItem->getVirtualStock());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getCurrentLevel());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getLevels()->first());

        $inventoryItem->adjustStockLevels('manual', -5, -10);

        $this->assertEquals(5, $inventoryItem->getStock());
        $this->assertEquals(10, $inventoryItem->getAllocatedStock());
        $this->assertEquals(-5, $inventoryItem->getVirtualStock());
    }
}
