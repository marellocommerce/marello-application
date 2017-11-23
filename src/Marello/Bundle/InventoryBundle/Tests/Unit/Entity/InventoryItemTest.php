<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class InventoryItemTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new InventoryItem(new Warehouse(), new Product()), [
            ['id', 42],
            ['desiredInventory', 42],
            ['purchaseInventory', 42],
            ['replenishment', 'some string']
        ]);

        $this->assertPropertyCollections(new InventoryItem(new Warehouse(), new Product()), [
            ['inventoryLevels', new InventoryLevel()],
        ]);
    }
}
