<?php

namespace Marello\Bundle\ReturnBundle\Validator;

use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReturnItemValidator extends ConstraintValidator
{
    /**
     * Validates if number of returned items is not greater than quantity ordered.
     *
     * @param            $returnItem
     * @param Constraint $constraint
     */
    public function validate($returnItem, Constraint $constraint)
    {
        if (!$returnItem instanceof ReturnItem) {
            return;
        }

        $orderItem = $returnItem->getOrderItem();

        /*
         * If no order item is defined for return item, it can not be validated because it is being validated just by
         * itself.
         */
        if (!$orderItem) {
            return;
        }

        /*
         * Reduce all return items into a sum of their quantities and add validated item quantity.
         * Get previous returned items and get the quantity of all and reduce them to a single value
         */
        $returnedQuantity = array_reduce(
            $orderItem->getReturnItems()->toArray(),
            function ($carry, ReturnItem $item) use ($constraint, $returnItem) {
                if ((!$constraint->includeSelf) && ($item === $returnItem)) {
                    return $carry;
                }
                return $carry + $item->getQuantity();
            },
            0
        );

        // total returned quantity (previous returned quantity + currently returned quantity)
        $returnedQuantity += $returnItem->getQuantity();

        /*
         * If returned quantity is greater than ordered, create a constraint violation.
         */
        if ($returnedQuantity > $orderItem->getQuantity()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('quantity')
                ->addViolation();
        }
    }
}
