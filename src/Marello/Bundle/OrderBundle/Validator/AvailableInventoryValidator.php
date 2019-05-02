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

namespace Marello\Bundle\OrderBundle\Validator;

use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Validator\Constraints\AvailableInventory;

class AvailableInventoryValidator extends ConstraintValidator
{
    const SALES_CHANNEL_FIELD = 'salesChannel';
    const PRODUCT_FIELD = 'product';
    const QUANTITY_FIELD = 'quantity';

    /** @var DoctrineHelper $doctrineHelper */
    private $doctrineHelper;

    /** @var AvailableInventoryProvider $availableInventoryProvider */
    private $availableInventoryProvider;

    /**
     * {@inheritdoc}
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        AvailableInventoryProvider $availableInventoryProvider
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->availableInventoryProvider = $availableInventoryProvider;
    }

    /**
     * Checks if the passed entity is unique in collection.
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof AvailableInventory) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\AvailableInventory');
        }

        if (!is_array($constraint->fields)) {
            throw new UnexpectedTypeException($constraint->fields, 'array');
        }

        if (null !== $constraint->errorPath && !is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        $fields = $constraint->fields;

        if (0 === count($fields)) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        }

        $values = $this->entityGetFieldValues($entity, $fields);
        if ((!isset($values[self::PRODUCT_FIELD]) || $values[self::PRODUCT_FIELD] === null) ||
            (!isset($values[self::SALES_CHANNEL_FIELD]) || $values[self::SALES_CHANNEL_FIELD] === null)) {
            throw new ConstraintDefinitionException('Cannot get inventory when not all required values are set');
        }

        $result = $this->availableInventoryProvider
            ->getAvailableInventory($values[self::PRODUCT_FIELD], $values[self::SALES_CHANNEL_FIELD]);

        if (!isset($values[self::QUANTITY_FIELD])) {
            throw new ConstraintDefinitionException('Cannot compare values if because there nothing to compare');
        }

        if (!$this->compareValues($result, $values[self::QUANTITY_FIELD])) {
            $errorPath = $this->getErrorPathFromConfig($constraint, $fields);
            $this->context->buildViolation($constraint->message)
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
     * @throws ConstraintDefinitionException
     * @return array
     */
    private function entityGetFieldValues($entity, $fields)
    {
        $className = get_class($entity);
        $em = $this->doctrineHelper->getEntityManagerForClass($className);
        if (!$em) {
            throw new ConstraintDefinitionException(sprintf('No manager found for class %s', $className));
        }

        $classMetaData = $em->getClassMetadata($className);
        $results = [];
        /* @var $class \Doctrine\Common\Persistence\Mapping\ClassMetadata */
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
                continue;
            }
            if (is_object($value)) {
                if ($value instanceof Order && $value->getSalesChannel()) {
                    $value = $value->getSalesChannel();
                    $fieldName = self::SALES_CHANNEL_FIELD;
                }

                if ($value instanceof ProductInterface) {
                    $fieldName = self::PRODUCT_FIELD;
                }
            }


            $results[$fieldName] = $value;
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
}
