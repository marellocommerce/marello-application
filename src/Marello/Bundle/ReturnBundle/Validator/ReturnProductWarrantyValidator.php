<?php

namespace Marello\Bundle\ReturnBundle\Validator;

use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\ReturnBundle\Util\ReturnHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReturnProductWarrantyValidator extends ConstraintValidator
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

        $returnItems    = $value->getReturnItems();
        $order          = $value->getOrder();

        /*
         * Get items which should be validated for the Ror constraint
         */
        $items = [];
        $returnItems->map(function (ReturnItem $returnItem) use (&$items) {
            if ($returnItem->getReason() === $this->warrantyReason) {
                $items[] = $returnItem;
            }
        });

        /**
         * no items to validate for the Warranty constraint
         */
        if (count($items) <= 0) {
            return;
        }

        /*
         * If there is no item returned, create constraint violation.
         */
        if ($items <= 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('returnItems')
                ->addViolation();
        }
    }
}
