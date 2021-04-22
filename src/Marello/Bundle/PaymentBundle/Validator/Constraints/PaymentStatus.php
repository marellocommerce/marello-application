<?php

namespace Marello\Bundle\PaymentBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class PaymentStatus extends Constraint
{
    /** @var string */
    public $message = 'marello.payment.status.message';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'marello_payment.payment_status_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
