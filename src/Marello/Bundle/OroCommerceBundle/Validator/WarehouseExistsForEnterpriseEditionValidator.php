<?php

namespace Marello\Bundle\OroCommerceBundle\Validator;

use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\Validator\Constraints\WarehouseExistsForEnterpriseEditionConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class WarehouseExistsForEnterpriseEditionValidator extends ConstraintValidator
{
    /**
     * @param OroCommerceSettings $value
     * @param WarehouseExistsForEnterpriseEditionConstraint|Constraint $constraint
     *
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof OroCommerceSettings) {
            throw new UnexpectedTypeException($value, OroCommerceSettings::class);
        }

        if ($value->isEnterprise() && !$value->getWarehouse()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('warehouse')
                ->addViolation();
        }
    }
}
