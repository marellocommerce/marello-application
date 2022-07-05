<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Tests\Unit\Validator;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints\WarehouseAddedToLinkedGroup;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Validator\WarehouseAddedToLinkedGroupValidator;

class WarehouseAddedToLinkedGroupValidatorTest extends TestCase
{
    use EntityTrait;

    /**
     * @var Constraint|\PHPUnit\Framework\MockObject\MockObject
     */
    private $constraint;

    /**
     * @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var EntityManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $manager;

    /**
     * @var WarehouseAddedToLinkedGroupValidator
     */
    private $validator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->constraint = $this->createMock(WarehouseAddedToLinkedGroup::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->manager = $this->createMock(EntityManager::class);

        $this->validator = new WarehouseAddedToLinkedGroupValidator($this->manager);
        $this->validator->initialize($this->context);
    }

    /**
     * @covers validate
     */
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
     * @param bool $system
     * @param WarehouseChannelGroupLink|null $link
     * @param string $type
     * @param int $buildViolationTimes
     */
    public function testValidate($system, $link, $type, $buildViolationTimes)
    {
        /** @var WarehouseGroup $type */
        $group = $this->getEntity(WarehouseGroup::class, [
            'id' => 1,
            'system' => $system,
            'warehouseChannelGroupLink' => $link
        ]);
        /** @var WarehouseType $type */
        $type = $this->getEntity(WarehouseType::class, [], ['name' => $type]);
        /** @var Warehouse $value */
        $value = $this->getEntity(
            Warehouse::class,
            ['id' => 1, 'warehouseType' => $type, 'group' => $group],
            ['label' => 'label', 'default' => false]
        );

        $uow = $this->createMock(UnitOfWork::class);
        $uow
            ->expects(static::exactly($buildViolationTimes))
            ->method('getOriginalEntityData')
            ->willReturn(['warehouse_type' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_VIRTUAL]);

        $this->manager
            ->expects(static::exactly($buildViolationTimes))
            ->method('getUnitOfWork')
            ->willReturn($uow);

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
                'link' => new WarehouseChannelGroupLink(),
                'type' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_FIXED,
                'buildViolationTimes' => 1
            ],
            'noViolationWithGlobalType' => [
                'system' => false,
                'link' => null,
                'type' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL,
                'buildViolationTimes' => 0
            ],
            'noViolationSystemGroup' => [
                'system' => true,
                'link' => null,
                'type' => WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL,
                'buildViolationTimes' => 0
            ]
        ];
    }
}
