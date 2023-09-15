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
use Marello\Bundle\CoreBundle\Validator\Constraints\UniqueEntityCollection;
use Marello\Bundle\CoreBundle\Validator\Exception\InvalidMethodException;

class UniqueEntityCollectionValidator extends ConstraintValidator
{
    /** @var array */
    private $collection = [];

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
        if (!$constraint instanceof UniqueEntityCollection) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\UniqueEntityCollection');
        }

        if (!is_array($constraint->fields) && !is_string($constraint->fields)) {
            throw new UnexpectedTypeException($constraint->fields, 'array');
        }

        if (null !== $constraint->errorPath && !is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        $fields = (array) $constraint->fields;
        if (0 === count($fields)) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        }

        $this->collection = [];
        $em = $this->registry->getManagerForClass(get_class($entity));
        $class = $em->getClassMetadata(get_class($entity));
        $fieldValue = null;

        /* @var $class \Doctrine\Persistence\Mapping\ClassMetadata */
        foreach ($fields as $fieldName) {
            if (!$class->hasField($fieldName) && !$class->hasAssociation($fieldName)) {
                throw new ConstraintDefinitionException(
                    sprintf(
                        'The field "%s" is not mapped by Doctrine, so it cannot be used for uniqueness.',
                        $fieldName
                    )
                );
            }

            $accessor = $this->getPropertyAccessor();
            $value = $accessor->getValue($entity, $fieldName);
            if (!$value) {
                throw new InvalidMethodException(
                    sprintf(
                        'Entity "%s" has no method public method for property %s',
                        get_class($entity),
                        $fieldName
                    )
                );
            }

            if (is_object($value)) {
                $value = $value->getId();
            }

            if ($fieldValue) {
                $fieldValue = sprintf('%s-%s-%s', $fieldName, $fieldValue, $value);
            } else {
                $fieldValue = $value;
            }
        }

        /*
         * There is already an item in the collection with this entity, create constraint violation.
         */
        $errorPath = null !== $constraint->errorPath ? $constraint->errorPath : $fields[0];
        if (in_array($fieldValue, $this->collection)) {
            $this->context->buildViolation($constraint->message)
                ->atPath($errorPath)
                ->addViolation();
        }

        $this->collection[] = $fieldValue;
    }

    /**
     * Get property accessor
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private function getPropertyAccessor()
    {
        return PropertyAccess::createPropertyAccessor();
    }
}
