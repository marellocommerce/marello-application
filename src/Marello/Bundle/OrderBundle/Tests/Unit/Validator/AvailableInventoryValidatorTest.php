<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Validator;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator;
use Marello\Bundle\OrderBundle\Validator\Constraints\AvailableInventory;
use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider;

class AvailableInventoryValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var AvailableInventory $constraint */
    protected $constraint;

    /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
    protected $context;

    /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject $doctrineHelper */
    protected $doctrineHelper;

    /** @var AvailableInventoryProvider|\PHPUnit_Framework_MockObject_MockObject $doctrineHelper */
    protected $inventoryProvider;

    /** @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject $objectManager */
    protected $objectManager;

    /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $classMetaData */
    protected $classMetaData;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->inventoryProvider = $this->createMock(AvailableInventoryProvider::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->classMetaData = $this->createMock(ClassMetadata::class);
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateWithoutFields()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('At least one field has to be specified.');
        $this->getValidator()->validate(null, $this->getConstraint(['fields' => []]));
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateWithoutCorrectTypeForFieldsOption()
    {
        $constraint = $this->getConstraint(['fields' => 'test']);
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            sprintf('Expected argument of type "array", "%s" given', gettype($constraint->fields))
        );
        $this->getValidator()->validate(null, $constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateWithInvalidEntityClass()
    {
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManagerForClass')
            ->willReturn(null);

        $this->objectManager->expects($this->never())
            ->method('getClassMetadata');

        $this->classMetaData->expects($this->never())
            ->method('hasField');

        $this->classMetaData->expects($this->never())
            ->method('hasAssociation');

        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage(
            sprintf('No manager found for class %s', null)
        );

        $this->getValidator()->validate(null, $this->getConstraint());
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateWithInvalidEntityMapping()
    {
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManagerForClass')
            ->willReturn($this->objectManager);

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($this->classMetaData);

        $this->classMetaData->expects($this->exactly(1))
            ->method('hasField');

        $this->classMetaData->expects($this->exactly(1))
            ->method('hasAssociation');

        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage(
            sprintf('The field "%s" is not mapped by Doctrine on entity %s', 'test', null)
        );

        $this->getValidator()->validate(null, $this->getConstraint());
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateNoValueForProperty()
    {
        $entity = new \StdClass();
        $entity->test = null;

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManagerForClass')
            ->willReturn($this->objectManager);

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($this->classMetaData);

        $this->classMetaData->expects($this->atLeastOnce())
            ->method('hasField')
            ->willReturn(true);

        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage(
            'Cannot get inventory when not all required values are set'
        );

        $this->getValidator()->validate($entity, $this->getConstraint(['fields' => ['test']]));
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateViolationIsBuild()
    {
        $orderMock = $this->createMock(Order::class);
        $salesChannelMock = $this->createMock(SalesChannel::class);
        $entity = new \StdClass();
        $entity->product = $this->createMock(Product::class);
        $entity->order = $orderMock;
        $entity->quantity = 100;
        $constraint = $this->getConstraint([
            'fields' => ['quantity', 'product', 'order'],
            'errorPath' => 'quantity'
        ]);
        $violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManagerForClass')
            ->willReturn($this->objectManager);

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($this->classMetaData);

        $this->classMetaData->expects($this->atLeastOnce())
            ->method('hasField')
            ->willReturn(true);

        $orderMock->expects($this->atLeastOnce())
            ->method('getSalesChannel')
            ->willReturn($salesChannelMock);

        $this->inventoryProvider->expects($this->exactly(1))
            ->method('getAvailableInventory')
            ->with($entity->product, $salesChannelMock)
            ->willReturn(10);

        $this->context->expects($this->exactly(1))
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilderMock);

        $violationBuilderMock->expects($this->exactly(1))
            ->method('atPath')
            ->with(AvailableInventoryValidator::QUANTITY_FIELD)
            ->willReturnSelf();

        $violationBuilderMock->expects($this->exactly(1))
            ->method('addViolation');

        $this->getValidator()->validate($entity, $constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function testQuantityFieldIsNotSet()
    {
        $orderMock = $this->createMock(Order::class);
        $salesChannelMock = $this->createMock(SalesChannel::class);
        $entity = new \StdClass();
        $entity->product = $this->createMock(Product::class);
        $entity->order = $orderMock;
        $constraint = $this->getConstraint([
            'fields' => ['product', 'order'],
            'errorPath' => 'quantity'
        ]);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManagerForClass')
            ->willReturn($this->objectManager);

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($this->classMetaData);

        $this->classMetaData->expects($this->atLeastOnce())
            ->method('hasField')
            ->willReturn(true);

        $orderMock->expects($this->atLeastOnce())
            ->method('getSalesChannel')
            ->willReturn($salesChannelMock);

        $this->inventoryProvider->expects($this->exactly(1))
            ->method('getAvailableInventory')
            ->with($entity->product, $salesChannelMock)
            ->willReturn(100);

        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage(
            'Cannot compare values if because there nothing to compare'
        );

        $this->getValidator()->validate($entity, $constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function testValidationIsSuccessful()
    {
        $orderMock = $this->createMock(Order::class);
        $salesChannelMock = $this->createMock(SalesChannel::class);
        $entity = new \StdClass();
        $entity->product = $this->createMock(Product::class);
        $entity->order = $orderMock;
        $entity->quantity = 100;
        $constraint = $this->getConstraint([
            'fields' => ['quantity', 'product', 'order'],
            'errorPath' => 'quantity'
        ]);
        $violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManagerForClass')
            ->willReturn($this->objectManager);

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($this->classMetaData);

        $this->classMetaData->expects($this->atLeastOnce())
            ->method('hasField')
            ->willReturn(true);

        $orderMock->expects($this->atLeastOnce())
            ->method('getSalesChannel')
            ->willReturn($salesChannelMock);

        $this->inventoryProvider->expects($this->exactly(1))
            ->method('getAvailableInventory')
            ->with($entity->product, $salesChannelMock)
            ->willReturn(100);

        $this->context->expects($this->never())
            ->method('buildViolation');

        $violationBuilderMock->expects($this->never())
            ->method('atPath');

        $violationBuilderMock->expects($this->never())
            ->method('addViolation');

        $this->getValidator()->validate($entity, $constraint);
    }

    /**
     * @param $options array
     * @return AvailableInventory
     */
    protected function getConstraint($options = null)
    {
        if (!$options) {
            $options = ['fields' => ['test', 'test2']];
        }
        return new AvailableInventory($options);
    }

    /**
     * @return AvailableInventoryValidator
     */
    protected function getValidator()
    {
        $validator = new AvailableInventoryValidator(
            $this->doctrineHelper,
            $this->inventoryProvider
        );
        $validator->initialize($this->context);

        return $validator;
    }
}
