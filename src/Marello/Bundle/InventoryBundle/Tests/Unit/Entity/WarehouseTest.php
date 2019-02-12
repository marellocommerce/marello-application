<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;

class WarehouseTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new Warehouse(), [
            ['id', 42],
            ['label', 'some string'],
            ['code', 'some string'],
            ['default', 1, false],
            ['owner', new Organization()],
            ['address', new MarelloAddress()],
            ['warehouseType', new WarehouseType('some name')],
            ['group', new WarehouseGroup()]
        ]);
    }
}
