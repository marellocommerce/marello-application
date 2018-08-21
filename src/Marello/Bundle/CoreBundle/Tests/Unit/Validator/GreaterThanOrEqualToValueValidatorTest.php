<?php

namespace Marello\Bundle\CoreBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

use Marello\Bundle\CoreBundle\Validator\Exception\InvalidMethodException;
use Marello\Bundle\CoreBundle\Validator\GreaterThanOrEqualToValueValidator;
use Marello\Bundle\CoreBundle\Validator\Constraints\GreaterThanOrEqualToValue;

class GreaterThanOrEqualToValueValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var GreaterThanOrEqualToValue $constraint */
    protected $constraint;

    /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
    protected $context;

    /** @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject $managerRegistry */
    protected $managerRegistry;

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
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->classMetaData = $this->createMock(ClassMetadata::class);
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateWithoutFields()
    {
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Exactly two fields need to be specified. You specified 0');
        $this->getValidator()->validate(null, $this->getConstraint(['fields' => []]));

        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Exactly two fields need to be specified. You specified 1');
        $this->getValidator()->validate(null, $this->getConstraint(['fields' => ['somefield']]));
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
        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
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
        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
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
    public function testValidateNoValueSetForProperty()
    {
        $entity = new \StdClass();
        $entity->test = null;

        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($this->objectManager);

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($this->classMetaData);

        $this->classMetaData->expects($this->atLeastOnce())
            ->method('hasField')
            ->willReturn(true);

        $this->expectException(InvalidMethodException::class);
        $this->expectExceptionMessage(
            sprintf('Entity "%s" has no value set for property %s', 'stdClass', 'test')
        );

        $this->getValidator()->validate($entity, $this->getConstraint());
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateViolationIsBuild()
    {
        $entity = new \StdClass();
        $entity->test = 'test';
        $entity->test2 = 'test2';

        $violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($this->objectManager);

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($this->classMetaData);

        $this->classMetaData->expects($this->atLeastOnce())
            ->method('hasField')
            ->willReturn(true);

        $this->context->expects($this->exactly(1))
            ->method('buildViolation')
            ->with(sprintf('Field %s needs to be greater than or equal to field %s', $entity->test, $entity->test2))
            ->willReturn($violationBuilderMock);

        $violationBuilderMock->expects($this->exactly(1))
            ->method('atPath')
            ->with($entity->test)
            ->willReturnSelf();

        $violationBuilderMock->expects($this->exactly(1))
            ->method('addViolation');

        $this->getValidator()->validate($entity, $this->getConstraint());
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateViolationIsNotBuild()
    {
        $entity = new \StdClass();
        $entity->test = 2;
        $entity->test2 = 1;

        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($this->objectManager);

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($this->classMetaData);

        $this->classMetaData->expects($this->atLeastOnce())
            ->method('hasField')
            ->willReturn(true);

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->getValidator()->validate($entity, $this->getConstraint());
    }

    /**
     * @param $options array
     * @return GreaterThanOrEqualToValue
     */
    protected function getConstraint($options = null)
    {
        if (!$options) {
            $options = ['fields' => ['test', 'test2']];
        }
        return new GreaterThanOrEqualToValue($options);
    }

    /**
     * @return GreaterThanOrEqualToValueValidator
     */
    protected function getValidator()
    {
        $validator = new GreaterThanOrEqualToValueValidator($this->managerRegistry);
        $validator->initialize($this->context);

        return $validator;
    }
}
