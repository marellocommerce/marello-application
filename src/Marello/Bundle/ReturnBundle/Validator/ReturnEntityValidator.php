<?php

namespace Marello\Bundle\ReturnBundle\Validator;

use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReturnEntityValidator extends ConstraintValidator
{

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
         * Compute the amount of returned items.
         */
        $sum = 0;
        $returnItems->map(function (ReturnItem $returnItem) use (&$sum) {
            $sum += $returnItem->getQuantity();
        });

        /*
         * If there is no item returned, create constraint violation.
         */
        if ($sum <= 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('returnItems')
                ->addViolation();
        }
    }
}
