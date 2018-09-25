<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator\Constraints;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\WarehouseAddedToLinkedGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\WarehouseAddedToLinkedGroupValidator;

class WarehouseAddedToLinkedGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WarehouseAddedToLinkedGroup
     */
    protected $warehouseAddedToLinkedGroup;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->warehouseAddedToLinkedGroup = new WarehouseAddedToLinkedGroup([]);
    }

    public function testGetTargets()
    {
        static::assertEquals(
            WarehouseAddedToLinkedGroup::CLASS_CONSTRAINT,
            $this->warehouseAddedToLinkedGroup->getTargets()
        );
    }

    public function testValidatedBy()
    {
        static::assertEquals(
            WarehouseAddedToLinkedGroupValidator::ALIAS,
            $this->warehouseAddedToLinkedGroup->validatedBy()
        );
    }
}
