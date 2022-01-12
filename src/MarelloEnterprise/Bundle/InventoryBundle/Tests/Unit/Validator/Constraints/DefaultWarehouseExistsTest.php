<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator\Constraints;

use PHPUnit\Framework\TestCase;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\DefaultWarehouseExistsValidator;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\DefaultWarehouseExists;

class DefaultWarehouseExistsTest extends TestCase
{
    /**
     * @var DefaultWarehouseExists
     */
    protected $defaultWarehouseExists;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->defaultWarehouseExists = new DefaultWarehouseExists([]);
    }

    public function testGetTargets()
    {
        static::assertEquals(DefaultWarehouseExists::CLASS_CONSTRAINT, $this->defaultWarehouseExists->getTargets());
    }

    public function testValidatedBy()
    {
        static::assertEquals(DefaultWarehouseExistsValidator::ALIAS, $this->defaultWarehouseExists->validatedBy());
    }
}
