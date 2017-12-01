<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class WarehouseTest extends \PHPUnit_Framework_TestCase
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
