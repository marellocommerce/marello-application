<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\DefaultWarehouseExists;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use MarelloEnterprise\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\DefaultWarehouseExistsValidator;

class DefaultWarehouseExistsValidatorTest extends TestCase
{
    use EntityTrait;

    /**
     * @var WarehouseRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $warehouseRepository;

    /**
     * @var Constraint|\PHPUnit\Framework\MockObject\MockObject
     */
    private $constraint;

    /**
     * @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $aclHelper;

    /**
     * @var DefaultWarehouseExistsValidator
     */
    private $validator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->warehouseRepository = $this->createMock(WarehouseRepository::class);
        $this->constraint = $this->createMock(DefaultWarehouseExists::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $this->validator = new DefaultWarehouseExistsValidator($this->warehouseRepository, $this->aclHelper);
        $this->validator->initialize($this->context);
    }

    public function testValidateForWrongObject()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            'Expected argument of type "Marello\Bundle\InventoryBundle\Entity\Warehouse", "null" given'
        );
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
