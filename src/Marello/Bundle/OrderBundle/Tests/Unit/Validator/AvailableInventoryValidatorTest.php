<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\Mapping\ClassMetadata;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\OrderBundle\Validator\AvailableInventoryValidator;
use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Validator\Constraints\AvailableInventoryConstraint;

class AvailableInventoryValidatorTest extends TestCase
{
    /** @var AvailableInventoryConstraint $constraint */
    protected $constraint;

    /** @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject $context */
    protected $context;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $doctrineHelper;

    /** @var AvailableInventoryProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $inventoryProvider;

    /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $objectManager;

    /** @var ClassMetadata|\PHPUnit\Framework\MockObject\MockObject */
    protected $classMetaData;

    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $eventDispatcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->inventoryProvider = $this->createMock(AvailableInventoryProvider::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->classMetaData = $this->createMock(ClassMetadata::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
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

        $this->getValidator()->validate(new \StdClass(), $this->getConstraint());
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

        $this->getValidator()->validate(new \StdClass(), $this->getConstraint());
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateViolationIsBuild()
    {
        $orderMock = $this->createMock(Order::class);
        $salesChannelMock = $this->createMock(SalesChannel::class);
        $product = $this->createMock(Product::class);
        $entity = new OrderItem();
        $entity->setProduct($product);
        $entity->setOrder($orderMock);
        $entity->setQuantity(100);
        $constraint = $this->getConstraint([
            'fields' => ['quantity', 'product', 'order'],
            'errorPath' => 'quantity'
        ]);
        $product->expects($this->once())->method('getSuppliers')->willReturn(new ArrayCollection());
        $product->expects($this->exactly(3))->method('getInventoryItem')->willReturn(null);
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
            ->with($product, $salesChannelMock)
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
            ->method('setParameter')
            ->willReturnSelf();

        $violationBuilderMock->expects($this->exactly(1))
            ->method('addViolation');

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
     * @return AvailableInventoryConstraint
     */
    protected function getConstraint($options = null)
    {
        if (!$options) {
            $options = ['fields' => ['test', 'test2']];
        }
        return new AvailableInventoryConstraint($options);
    }

    /**
     * @return AvailableInventoryValidator
     */
    protected function getValidator()
    {
        $validator = new AvailableInventoryValidator(
            $this->doctrineHelper,
            $this->inventoryProvider,
            $this->eventDispatcher
        );

        $validator->initialize($this->context);

        return $validator;
    }
}
