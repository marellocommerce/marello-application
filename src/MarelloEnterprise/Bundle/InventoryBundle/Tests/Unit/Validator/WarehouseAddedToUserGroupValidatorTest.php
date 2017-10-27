<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

use Oro\Component\Testing\Unit\EntityTrait;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\WarehouseAddedToUserGroupValidator;

class WarehouseAddedToUserGroupValidatorTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var Constraint|\PHPUnit_Framework_MockObject_MockObject
     */
    private $constraint;

    /**
     * @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var WarehouseAddedToUserGroupValidator
     */
    private $validator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->constraint = $this->createMock(Constraint::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);

        $this->validator = new WarehouseAddedToUserGroupValidator();
        $this->validator->initialize($this->context);
    }

    /**
     * @covers validate
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "Marello\Bundle\InventoryBundle\Entity\Warehouse",
     * "NULL" given
     */
    public function testValidateForWrongObject()
    {
        $this->validator->validate(null, $this->constraint);
    }

    /**
     * @dataProvider validateDataProvider
     * @param bool $system
     * @param string $type
     * @param int $buildViolationTimes
     */
    public function testValidate($system, $type, $buildViolationTimes)
    {
        /** @var WarehouseGroup $type */
        $group = $this->getEntity(WarehouseGroup::class, ['id' => 1], ['system' => $system]);
        /** @var WarehouseType $type */
        $type = $this->getEntity(WarehouseType::class, [], ['name' => $type]);
        /** @var Warehouse $value */
        $value = $this->getEntity(
            Warehouse::class,
            ['id' => 1, 'warehouseType' => $type, 'group' => $group],
            ['label' => 'label', 'default' => false]
        );

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects(static::exactly($buildViolationTimes))
            ->method('buildViolation')
            ->willReturn($builder);

        $builder->expects(static::exactly($buildViolationTimes))
            ->method('atPath')
            ->with('warehouseType')
            ->willReturn($builder);

        $this->validator->validate($value, $this->constraint);
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            'withViolation' => [
                'system' => false,
                'type' => WarehouseTypeProviderInterface::FIXED_TYPE,
                'buildViolationTimes' => 1
            ],
            'noViolationWithGlobalType' => [
                'system' => false,
                'type' => WarehouseTypeProviderInterface::GLOBAL_TYPE,
                'buildViolationTimes' => 0
            ],
            'noViolationSystemGroup' => [
                'system' => true,
                'type' => WarehouseTypeProviderInterface::GLOBAL_TYPE,
                'buildViolationTimes' => 0
            ]
        ];
    }
}
