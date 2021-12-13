<?php

namespace Marello\Bundle\RefundBundle\Validator;

use Marello\Bundle\RefundBundle\Calculator\RefundBalanceCalculator;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RefundBalanceValidator extends ConstraintValidator
{
    /**
     * @var RefundBalanceCalculator
     */
    protected $refundBalanceCalculator;

    /**
     * @param RefundBalanceCalculator $refundBalanceCalculator
     */
    public function __construct(RefundBalanceCalculator $refundBalanceCalculator)
    {
        $this->refundBalanceCalculator = $refundBalanceCalculator;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Refund) {
            return;
        }

        if ($this->refundBalanceCalculator->caclulateBalance($value) < 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('refund')
                ->addViolation();
        }
    }
}
