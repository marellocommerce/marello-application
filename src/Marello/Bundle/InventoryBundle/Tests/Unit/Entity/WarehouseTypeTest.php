<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class WarehouseTypeTest extends \PHPUnit_Framework_TestCase
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
