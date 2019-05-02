<?php

namespace Marello\Bundle\PurchaseOrderBundle\Validator;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PurchaseOrderItemValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof PurchaseOrderItem) {
            return;
        }

        if ($value->getProduct() == null) {
            $this->context
                ->buildViolation($constraint->productMessage)
                ->atPath('product')
                ->addViolation()
            ;
        }

        if ($value->getOrderedAmount() == null || $value->getOrderedAmount() <= 0) {
            $this->context
                ->buildViolation($constraint->orderedAmountMessage)
                ->atPath('orderedAmount')
                ->addViolation()
            ;
        }

        if ($value->getPurchasePrice() == null || $value->getPurchasePrice()->getValue() <= 0) {
            $this->context
                ->buildViolation($constraint->purchasePriceMessage)
                ->atPath('purchasePrice.value')
                ->addViolation()
            ;
        }
    }
}
