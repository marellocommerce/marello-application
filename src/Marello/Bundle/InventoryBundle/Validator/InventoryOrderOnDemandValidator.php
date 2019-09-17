<?php

namespace Marello\Bundle\InventoryBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryOrderOnDemandValidator extends ConstraintValidator
{
    /**
     * Checks if the passed entity is unique in collection.
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof InventoryItem) {
            return;
        }
        $product = $entity->getProduct();
        if ($entity->isOrderOnDemandAllowed() && !$product->getPreferredSupplier()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('orderOnDemandAllowed')
                ->addViolation();
        }
    }
}
