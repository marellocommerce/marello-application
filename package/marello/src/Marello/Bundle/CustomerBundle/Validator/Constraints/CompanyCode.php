<?php

namespace Marello\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class CompanyCode extends Constraint
{
    /**
     * @var string
     */
    public $message = 'marello.customer.company.code.message';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'marello_customer.company_code_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
