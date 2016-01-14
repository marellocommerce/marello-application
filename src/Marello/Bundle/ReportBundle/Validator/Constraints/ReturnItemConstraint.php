<?php

namespace Marello\Bundle\ReportBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ReturnItemConstraint extends Constraint
{
    /** @var string */
    public $message = 'Returned quantity is greater than ordered.';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'Marello\Bundle\ReportBundle\Validator\ReturnItemValidator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
