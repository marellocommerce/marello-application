<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\UPSBundle\Validator\Constraints\RemoveUsedShippingServiceConstraint;

class RemoveUsedShippingServiceConstraintTest extends TestCase
{
    public function testValidatedBy()
    {
        $constraint = new RemoveUsedShippingServiceConstraint();

        static::assertSame('marello_ups_remove_used_shipping_service_validator', $constraint->validatedBy());
    }

    public function testGetTargets()
    {
        $constraint = new RemoveUsedShippingServiceConstraint();

        static::assertSame(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
