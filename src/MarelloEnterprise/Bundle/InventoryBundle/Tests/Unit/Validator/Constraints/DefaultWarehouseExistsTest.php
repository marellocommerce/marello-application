<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator\Constraints;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\DefaultWarehouseExists;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\DefaultWarehouseExistsValidator;

class DefaultWarehouseExistsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultWarehouseExists
     */
    protected $defaultWarehouseExists;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
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
