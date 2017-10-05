<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator\Constraints;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\WarehouseAddedToUserGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\WarehouseAddedToUserGroupValidator;

class WarehouseAddedToUserGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WarehouseAddedToUserGroup
     */
    protected $warehouseAddedToUserGroup;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->warehouseAddedToUserGroup = new WarehouseAddedToUserGroup([]);
    }

    public function testGetTargets()
    {
        static::assertEquals(
            WarehouseAddedToUserGroup::CLASS_CONSTRAINT,
            $this->warehouseAddedToUserGroup->getTargets()
        );
    }

    public function testValidatedBy()
    {
        static::assertEquals(
            WarehouseAddedToUserGroupValidator::ALIAS,
            $this->warehouseAddedToUserGroup->validatedBy()
        );
    }
}
