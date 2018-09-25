<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class InventoryLevelTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $warehouse = new Warehouse();
        $this->assertPropertyAccessors(new InventoryLevel(), [
            ['id', 42],
            ['inventoryItem', new InventoryItem($warehouse, new Product())],
            ['warehouse', $warehouse],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()]
        ]);
    }
}
