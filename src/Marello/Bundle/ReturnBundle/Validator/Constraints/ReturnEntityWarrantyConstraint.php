<?php
namespace Marello\Bundle\ReturnBundle\Validator\Constraints;

use Marello\Bundle\ReturnBundle\Validator\ReturnWarrantyEntityValidator;
use Symfony\Component\Validator\Constraint;

class ReturnWarrantyEntityConstraint extends Constraint
{
    /** @var string */
    public $message = 'Returns can only contain either warranty or right of return items.';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return ReturnWarrantyEntityValidator::class;
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
