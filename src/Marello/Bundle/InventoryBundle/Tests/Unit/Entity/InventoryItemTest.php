<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

class InventoryItemTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new InventoryItem(new Product()), [
            ['id', 42],
            ['desiredInventory', 42],
            ['purchaseInventory', 42],
            ['replenishment', 'some string'],
            ['unitOfMeasurement', 'some measurement']
        ]);

        $this->assertPropertyCollections(new InventoryItem(new Product()), [
            ['inventoryLevels', new InventoryLevel()],
        ]);
    }
}
