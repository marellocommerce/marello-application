<?php

namespace Marello\Bundle\PurchaseOrderBundle\Validator\Constraints;

use Marello\Bundle\PurchaseOrderBundle\Validator\PurchaseOrderValidator;
use Symfony\Component\Validator\Constraint;

class PurchaseOrderConstraint extends Constraint
{
    /** @var string */
    public $message = 'At least one item should be added.';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return PurchaseOrderValidator::class;
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
