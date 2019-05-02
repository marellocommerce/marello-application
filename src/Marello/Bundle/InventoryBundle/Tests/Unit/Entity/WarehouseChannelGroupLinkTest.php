<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;

class WarehouseChannelGroupLinkTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new WarehouseChannelGroupLink(), [
            ['id', 42],
            ['organization', new Organization()],
            ['warehouseGroup', new WarehouseGroup()],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()]
        ]);
        $this->assertPropertyCollections(new WarehouseChannelGroupLink(), [
            ['salesChannelGroups', new SalesChannelGroup()],
        ]);
    }
}
