<?php

namespace Marello\Bundle\InventoryBundle\Validator\Constraints;

use Marello\Bundle\InventoryBundle\Model\InventoryItemModify as InventoryItemModifyModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InventoryItemModifyValidator extends ConstraintValidator
{
    /**
     * @var ExecutionContextInterface
     */
    protected $context;

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof InventoryItemModifyModel) {
            return;
        }

        if ($value->getModifyOperator() === InventoryItemModifyModel::OPERATOR_DECREASE) {
            if (($value->getQuantity() - $value->getModifyAmount()) < 0) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->atPath('modifyAmount')
                    ->addViolation();
            }
        }
    }
}
