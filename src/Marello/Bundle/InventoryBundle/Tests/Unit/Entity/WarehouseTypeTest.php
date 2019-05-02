<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use Marello\Bundle\InventoryBundle\Entity\WarehouseType;

class WarehouseTypeTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new WarehouseType('some string'), [
            ['name', 'some string', false],
            ['label', 'some string'],
        ]);
    }
}
