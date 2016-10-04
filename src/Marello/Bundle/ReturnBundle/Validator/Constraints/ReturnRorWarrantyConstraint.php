<?php
namespace Marello\Bundle\ReturnBundle\Validator\Constraints;

use Marello\Bundle\ReturnBundle\Validator\ReturnRorWarrantyValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Ror stands for Right of return
 * Class ReturnRorWarrantyConstraint
 * @package Marello\Bundle\ReturnBundle\Validator\Constraints
 */
class ReturnRorWarrantyConstraint extends Constraint
{
    /** @var string */
    public $message = 'Cannot create return right of return has passed';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return ReturnRorWarrantyValidator::class;
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
