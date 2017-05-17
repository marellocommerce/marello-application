<?php

namespace Marello\Bundle\ReturnBundle\Tests\Unit\Validator;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\WarehouseDeleteValidator;

class WarehouseDeleteValidatorTest extends TestCase
{
    /** @var WarehouseDeleteValidator $validator */
    protected $validator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->validator = new WarehouseDeleteValidator();
    }

    /**
     * @param string $label
     * @param bool   $default
     *
     * @return Warehouse
     */
    protected function getTestWarehouse($label = 'Warehouse', $default = false)
    {
        return new Warehouse($label, $default);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            'VALID: Warehouse can be deleted'              => [$this->getTestWarehouse(), true],
            'INVALID: Default warehouse cannot be deleted' => [$this->getTestWarehouse('Warehouse 2', true), false]
        ];
    }

    /**
     * @dataProvider validateDataProvider
     *
     * @param Warehouse $warehouse
     * @param bool      $canBeDeleted
     */
    public function testValidate(Warehouse $warehouse, $canBeDeleted)
    {
        if ($canBeDeleted) {
            $this->assertTrue($this->validator->validate($warehouse));
        } else {
            $this->assertFalse($this->validator->validate($warehouse));
        }
    }
}
