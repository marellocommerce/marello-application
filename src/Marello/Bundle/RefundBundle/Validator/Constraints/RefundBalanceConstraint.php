<?php

namespace Marello\Bundle\RefundBundle\Validator\Constraints;

use Marello\Bundle\RefundBundle\Validator\RefundBalanceValidator;
use Symfony\Component\Validator\Constraint;

class RefundBalanceConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Sum of Refund Balance and Refund Amount can\'t be greater than Grand Total';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return RefundBalanceValidator::class;
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
