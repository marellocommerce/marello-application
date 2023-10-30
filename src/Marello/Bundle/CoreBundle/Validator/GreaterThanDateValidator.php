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
use Oro\Bundle\EntityBundle\ORM\Registry;
use Marello\Bundle\CoreBundle\Validator\Constraints\GreaterThanDate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GreaterThanDateValidator extends ConstraintValidator
{
    /** @var Registry */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Checks if the passed entity is unique in collection.
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof GreaterThanDate) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\GreaterThanDate');
        }

        if (null !== $constraint->field && !is_string($constraint->field)) {
            throw new UnexpectedTypeException($constraint->field, 'string');
        }

        if (null !== $constraint->nullable && !is_bool($constraint->nullable)) {
            throw new UnexpectedTypeException($constraint->nullable, 'boolean');
        }

        if (null !== $constraint->errorPath && !is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        if (null !== $constraint->greaterThan && !is_string($constraint->greaterThan)) {
            throw new UnexpectedTypeException($constraint->greaterThan, 'string or null');
        }

        $field = (string) $constraint->field;
        $greaterThan = (string) $constraint->greaterThan;
        $nullable = (bool) $constraint->nullable;


        if ('' === $field) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        }

        if ('' === $greaterThan || null === $greaterThan) {
            $greaterThan = 'today';
        }

        if (!$nullable) {
            $nullable = false;
        }

        $em = $this->registry->getManagerForClass(get_class($entity));
        $class = $em->getClassMetadata(get_class($entity));
        $fieldValue = null;

        /* @var $class \Doctrine\Persistence\Mapping\ClassMetadata */
        if (!$class->hasField($field) && !$class->hasAssociation($field)) {
            throw new ConstraintDefinitionException(
                sprintf(
                    'The field "%s" is not mapped by Doctrine, so it cannot be used for validation.',
                    $field
                )
            );
        }

        $function = sprintf('get%s', ucfirst($field));
        $fieldValue = $entity->$function();

        if (null === $fieldValue && $nullable == true) {
            return;
        }

        $errorPath = null !== $constraint->errorPath ? $constraint->errorPath : $field;
        if ($fieldValue && $fieldValue <= new \DateTime($greaterThan)) {
            $this->context->buildViolation($constraint->message, ['%date%' => $greaterThan])
                ->atPath($errorPath)
                ->addViolation();
        }
    }
}
