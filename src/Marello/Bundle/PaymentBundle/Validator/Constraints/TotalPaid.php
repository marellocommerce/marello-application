<?php

namespace Marello\Bundle\PaymentBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class TotalPaid extends Constraint
{
    /** @var string */
    public $message = 'marello.payment.total_paid.message';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'marello_payment.total_paid_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
