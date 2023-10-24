<?php

namespace Marello\Bundle\ReturnBundle\Validator\Constraints;

use Marello\Bundle\ReturnBundle\Validator\ReturnEntityValidator;
use Symfony\Component\Validator\Constraint;

class ReturnEntityConstraint extends Constraint
{
    /** @var string */
    public $message = 'At least one item should be returned.';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return ReturnEntityValidator::class;
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
