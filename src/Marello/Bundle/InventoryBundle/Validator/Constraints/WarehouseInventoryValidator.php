<?php

namespace Marello\Bundle\InventoryBundle\Validator\Constraints;

use Marello\Bundle\InventoryBundle\Model\WarehouseInventory as WarehouseInventoryModel;
use Marello\Bundle\InventoryBundle\Validator\Constraints\WarehouseInventory as WarehouseInventoryConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception;

class WarehouseInventoryValidator extends ConstraintValidator
{
    /**
     * @var ExecutionContextInterface
     */
    protected $context;

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed                                   $value      The value that should be validated
     * @param WarehouseInventoryConstraint|Constraint $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof WarehouseInventoryModel) {
            return;
        }

        if ($value->getModifyOperator() === WarehouseInventoryModel::OPERATOR_DECREASE) {
            if (($value->getQuantity() - $value->getModifyAmount()) < 0) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->atPath('modifyAmount')
                    ->addViolation();
            }
        }
    }
}
