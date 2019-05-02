<?php

namespace Marello\Bundle\UPSBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class RemoveUsedShippingServiceConstraint extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return RemoveUsedShippingServiceValidator::ALIAS;
    }

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
