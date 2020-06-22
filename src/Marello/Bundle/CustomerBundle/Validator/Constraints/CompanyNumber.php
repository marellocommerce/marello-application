<?php

namespace Marello\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class CompanyNumber extends Constraint
{
    /**
     * @var string
     */
    public $message = 'marello.customer.company.number.message';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'marello_customer.company_number_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
