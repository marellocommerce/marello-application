<?php

namespace Marello\Bundle\ReturnBundle\Validator\Constraints;

use Marello\Bundle\ReturnBundle\Validator\ReturnItemValidator;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ReturnItemConstraint extends Constraint
{
    /** @var string */
    public $message = 'Returned quantity is greater than ordered.';

    /** @var bool */
    public $includeSelf = true;

    /**
     * @return string
     */
    public function getDefaultOption()
    {
        return 'includeSelf';
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return ReturnItemValidator::class;
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
