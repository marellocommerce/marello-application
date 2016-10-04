<?php
namespace Marello\Bundle\ReturnBundle\Validator\Constraints;

use Marello\Bundle\ReturnBundle\Validator\ReturnProductWarrantyValidator;
use Symfony\Component\Validator\Constraint;

class ReturnWarrantyConstraint extends Constraint
{
    /** @var string */
    public $message = 'Cannot create return, product warranty has passed';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return ReturnProductWarrantyValidator::class;
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
