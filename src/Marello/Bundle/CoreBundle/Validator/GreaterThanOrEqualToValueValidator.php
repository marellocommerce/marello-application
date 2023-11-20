<?php
/**
 * This application uses Open Source components. You can find the source code
 * of their open source projects along with license information below. We acknowledge
 * and are grateful to these developers for their contributions to open source.
 *
 * This class is inspired by Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator
 * all efforts and inspiration which have paved the road for this belong to Fabien Potencier.
 *
 * Project: Symfony (https://symfony.com)
 * Copyright (c) 2004-2015 Fabien Potencier. All right reserved.
 */

namespace Marello\Bundle\CoreBundle\Validator;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Marello\Bundle\CoreBundle\Validator\Exception\InvalidMethodException;
use Marello\Bundle\CoreBundle\Validator\Constraints\GreaterThanOrEqualToValue;

class GreaterThanOrEqualToValueValidator extends ConstraintValidator
{
    public function __construct(
        protected ManagerRegistry $registry
    ) {
    }

    /**
     * Checks if the passed entity is unique in collection.
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof GreaterThanOrEqualToValue) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\GreaterThanOrEqualToValue');
        }

        if (!is_array($constraint->fields)) {
            throw new UnexpectedTypeException($constraint->fields, 'array');
        }

        if (null !== $constraint->errorPath && !is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        $fields = (array) $constraint->fields;
        if (2 !== count($fields)) {
            throw new ConstraintDefinitionException(
                sprintf('Exactly two fields need to be specified. You specified %s', count($fields))
            );
        }

        $values = $this->entityGetFieldValues($entity, $fields);
        $errorPath = $this->getErrorPathFromConfig($constraint, $fields);

        if (!$this->compareValues($values[0], $values[1])) {
            $message = $this->compileViolationMessage($constraint, $values);
            $this->context->buildViolation($message)
                ->atPath($errorPath)
                ->addViolation();
        }
    }

    /**
     * Comparison of the values
     * @param $value1
     * @param $value2
     * @return bool
     */
    protected function compareValues($value1, $value2)
    {
        return $value1 >= $value2;
    }

    /**
     * Get property accessor
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private function getPropertyAccessor()
    {
        return PropertyAccess::createPropertyAccessor();
    }

    /**
     * Get the values from the entity config or throw exception if the field doesn't exist
     * @param $entity
     * @param $fields
     * @throws ConstraintDefinitionException|InvalidMethodException
     * @return array
     */
    private function entityGetFieldValues($entity, $fields)
    {
        $className = get_class($entity);
        $em = $this->registry->getManagerForClass($className);
        if (!$em) {
            throw new ConstraintDefinitionException(sprintf('No manager found for class %s', $className));
        }

        $classMetaData = $em->getClassMetadata($className);
        $results = [];
        /* @var $class \Doctrine\Persistence\Mapping\ClassMetadata */
        foreach ($fields as $fieldName) {
            if (!$classMetaData->hasField($fieldName) && !$classMetaData->hasAssociation($fieldName)) {
                throw new ConstraintDefinitionException(
                    sprintf(
                        'The field "%s" is not mapped by Doctrine on entity %s',
                        $fieldName,
                        $className
                    )
                );
            }

            $accessor = $this->getPropertyAccessor();
            $value = $accessor->getValue($entity, $fieldName);
            if (null === $value) {
                throw new InvalidMethodException(
                    sprintf(
                        'Entity "%s" has no value set for property %s',
                        $className,
                        $fieldName
                    )
                );
            }

            if (is_object($value)) {
                $value = $value->getId();
            }

            $results[] = $value;
        }

        return $results;
    }

    /**
     * Get field to display Error from config or use the first field in the array
     * @param Constraint $constraint
     * @param $fields
     * @return mixed
     */
    private function getErrorPathFromConfig(Constraint $constraint, $fields)
    {
        return null !== $constraint->errorPath ? $constraint->errorPath : $fields[0];
    }

    /**
     * Compile message from the configured fields and constraint message
     * @param Constraint $constraint
     * @param array $fieldsAndValues
     * @return string
     */
    private function compileViolationMessage(Constraint $constraint, array $fieldsAndValues)
    {
        $message = $constraint->message;
        return sprintf($message, $fieldsAndValues[0], $fieldsAndValues[1]);
    }
}
