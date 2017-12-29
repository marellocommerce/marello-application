<?php

namespace Marello\Bundle\CoreBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;

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

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateWithoutRequiredOptions()
    {
        $constraint = new GreaterThanOrEqualToValue(['fields' => []]);
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Exactly two fields need to be specified. You specified 0');
        $this->getValidator()->validate('', $constraint);

        $constraint = new GreaterThanOrEqualToValue(['fields' => ['somefield']]);
        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Exactly two fields need to be specified. You specified 1');
        $this->getValidator()->validate('', $constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateWithoutCorrectTypeForFieldsOption()
    {
        $constraint = new GreaterThanOrEqualToValue(['fields' => 'test']);
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(sprintf('Expected argument of type "array", "%s" given', gettype($constraint->fields)));
        $this->getValidator()->validate('', $constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateWithoutValidEntityClass()
    {
        $constraint = new GreaterThanOrEqualToValue(['fields' => ['test', 'test2']]);

        $objManagerMock = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $metaDataMock = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(null)
            ->willReturn(null);

        $objManagerMock->expects($this->never())
            ->method('getClassMetadata');

        $metaDataMock->expects($this->never())
            ->method('hasField');

        $metaDataMock->expects($this->never())
            ->method('hasAssociation');

        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage(
            sprintf('No manager found for class %s', null)
        );

        $this->getValidator()->validate(null, $constraint);

    }


    public function testValidateWithInValidEntityMapping()
    {
        $constraint = new GreaterThanOrEqualToValue(['fields' => ['test', 'test2']]);

        $objManagerMock = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $metaDataMock = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($objManagerMock);

        $objManagerMock->expects($this->once())
            ->method('getClassMetadata')
            ->willReturn($metaDataMock);

        $metaDataMock->expects($this->exactly(1))
            ->method('hasField');

        $metaDataMock->expects($this->exactly(1))
            ->method('hasAssociation');

        $this->expectException(ConstraintDefinitionException::class);
        $this->expectExceptionMessage(
            sprintf('The field "%s" is not mapped by Doctrine on entity %s', 'test', null)
        );

        $this->getValidator()->validate(null, $constraint);

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
