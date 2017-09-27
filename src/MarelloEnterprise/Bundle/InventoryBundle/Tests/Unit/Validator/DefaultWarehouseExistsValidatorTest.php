<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\DefaultWarehouseExistsValidator;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DefaultWarehouseExistsValidatorTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var WarehouseRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $warehouseRepository;

    /**
     * @var Constraint|\PHPUnit_Framework_MockObject_MockObject
     */
    private $constraint;

    /**
     * @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var DefaultWarehouseExistsValidator
     */
    private $validator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->warehouseRepository = $this->getMockBuilder(WarehouseRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->constraint = $this->createMock(Constraint::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);

        $this->validator = new DefaultWarehouseExistsValidator($this->warehouseRepository);
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
     * @param bool $default
     * @param array $otherDefaults
     * @param int $callRepositoryTimes
     * @param int $buildViolationTimes
     */
    public function testValidate($default, $otherDefaults, $callRepositoryTimes, $buildViolationTimes)
    {
        /** @var Warehouse $value */
        $value = $this->getEntity(Warehouse::class, ['id' => 1], ['label' => 'label', 'default' => $default]);

        $this->warehouseRepository
            ->expects(static::exactly($callRepositoryTimes))
            ->method('getDefaultExcept')
            ->with($value->getId())
            ->willReturn($otherDefaults);

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects(static::exactly($buildViolationTimes))
            ->method('buildViolation')
            ->willReturn($builder);

        $builder->expects(static::exactly($buildViolationTimes))
            ->method('atPath')
            ->with('default')
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
                'default' => false,
                'otherDefaults' => [],
                'callRepositoryTimes' => 1,
                'buildViolationTimes' => 1
            ],
            'noViolationWithDefault' => [
                'default' => true,
                'otherDefaults' => [],
                'callRepositoryTimes' => 0,
                'buildViolationTimes' => 0
            ],
            'noViolationOtherDefaultExists' => [
                'default' => false,
                'otherDefaults' => [1, 2, 3],
                'callRepositoryTimes' => 1,
                'buildViolationTimes' => 0
            ]
        ];
    }
}
