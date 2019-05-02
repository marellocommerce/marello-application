<?php

namespace Marello\Bundle\PurchaseOrderBundle\Validator;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PurchaseOrderValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof PurchaseOrder) {
            return;
        }

        if ($value->getItems()->count() === 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('items')
                ->addViolation();
        }
    }
}
