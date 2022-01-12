<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator\Constraints;

use PHPUnit\Framework\TestCase;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\WarehouseAddedToLinkedGroupValidator;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\WarehouseAddedToLinkedGroup;

class WarehouseAddedToLinkedGroupTest extends TestCase
{
    /**
     * @var WarehouseAddedToLinkedGroup
     */
    protected $warehouseAddedToLinkedGroup;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
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
