<?php

namespace Marello\Bundle\ReturnBundle\Validator;

use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReturnWarrantyEntityValidator extends ConstraintValidator
{
    protected $warrantyReason = 'warranty';

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof ReturnEntity) {
            return;
        }

        $returnItems = $value->getReturnItems();

        /*
         * Count different type of return reason items.
         */
        $warrantyItems = 0;
        $rorItems = 0;
        $returnItems->map(function (ReturnItem $returnItem) use (&$rorItems, &$warrantyItems) {
            if ($returnItem->getReason() !== $this->warrantyReason) {
                $rorItems++;
            } else {
                $warrantyItems++;
            }
        });

        /*
         * If both type of return reasons are in 1 return, create violation
         */
        if ($warrantyItems > 0 && $rorItems > 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('returnItems')
                ->addViolation();
        }
    }
}
