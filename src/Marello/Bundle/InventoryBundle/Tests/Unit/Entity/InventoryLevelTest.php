<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;

class InventoryLevelTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $warehouse = new Warehouse();
        $this->assertPropertyAccessors(new InventoryLevel(), [
            ['id', 42],
            ['inventoryItem', new InventoryItem($warehouse, new Product())],
            ['warehouse', $warehouse],
            ['pickLocation', '12-4-16', false],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()]
        ]);
    }
}
