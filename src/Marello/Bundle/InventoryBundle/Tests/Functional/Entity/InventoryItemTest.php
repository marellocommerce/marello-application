<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

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

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->initClient();

        $this->manager = $this->client->getContainer()->get('marello_inventory.manager.inventory_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateAndUpdateNewInventoryItem()
    {
        $product   = $this->prophesize(Product::class);
        $warehouse = $this->prophesize(Warehouse::class);

        $inventoryItem = new InventoryItem($warehouse->reveal(), $product->reveal());

        $this->assertEquals(0, $inventoryItem->getStock());
        $this->assertEquals(0, $inventoryItem->getAllocatedStock());
        $this->assertEquals(0, $inventoryItem->getVirtualStock());
        $this->assertNull($inventoryItem->getCurrentLevel());

        $data = $this->getContextData($inventoryItem, 'manual', 10, 20);
        $context = InventoryUpdateContext::createUpdateContext($data);
        $this->manager->updateInventoryItems($context);

        $this->assertEquals(10, $inventoryItem->getStock());
        $this->assertEquals(20, $inventoryItem->getAllocatedStock());
        $this->assertEquals(-10, $inventoryItem->getVirtualStock());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getCurrentLevel());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getLevels()->first());
    }

    /**
     * {@inheritdoc}
     */
    public function testIfInventoryItemIsUpdatedCorrectlyWithMultipleChanges()
    {
        $product   = $this->prophesize(Product::class);
        $warehouse = $this->prophesize(Warehouse::class);

        $inventoryItem = new InventoryItem($warehouse->reveal(), $product->reveal());

        $this->assertEquals(0, $inventoryItem->getStock());
        $this->assertEquals(0, $inventoryItem->getAllocatedStock());
        $this->assertEquals(0, $inventoryItem->getVirtualStock());
        $this->assertNull($inventoryItem->getCurrentLevel());

        $data = $this->getContextData($inventoryItem, 'manual', 10, 20);
        $context = InventoryUpdateContext::createUpdateContext($data);
        $this->manager->updateInventoryItems($context);

        $this->assertEquals(10, $inventoryItem->getStock());
        $this->assertEquals(20, $inventoryItem->getAllocatedStock());
        $this->assertEquals(-10, $inventoryItem->getVirtualStock());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getCurrentLevel());
        $this->assertInstanceOf(StockLevel::class, $inventoryItem->getLevels()->first());

        $data = $this->getContextData($inventoryItem, 'manual', -5, -10);
        $context = InventoryUpdateContext::createUpdateContext($data);
        $this->manager->updateInventoryItems($context);

        $this->assertEquals(5, $inventoryItem->getStock());
        $this->assertEquals(10, $inventoryItem->getAllocatedStock());
        $this->assertEquals(-5, $inventoryItem->getVirtualStock());
    }

    /**
     * Get Inventory Update context data
     * @param $item                 InventoryItem
     * @param $trigger              //change trigger
     * @param $inventory            //inventory to update
     * @param $allocatedInventory   //allocated inventory to update
     * @return array
     */
    protected function getContextData($item, $trigger, $inventory, $allocatedInventory)
    {
        $data = [
            'stock'             => $inventory,
            'allocatedStock'    => $allocatedInventory,
            'trigger'           => $trigger,
            'items'             => [
                [
                    'item'          => $item,
                    'qty'           => $inventory,
                    'allocatedQty'  => $allocatedInventory
                ]
            ]
        ];

        return $data;
    }
}
