<?php

namespace Marello\Bundle\UPSBundle\Tests\Unit\Validator\Constraints;

use Marello\Bundle\UPSBundle\Validator\Constraints\CountryShippingServicesConstraint;
use Symfony\Component\Validator\Constraint;

class CountryShippingServicesConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatedBy()
    {
        $constraint = new CountryShippingServicesConstraint();

        static::assertSame('marello_ups_country_shipping_services_validator', $constraint->validatedBy());
    }

    public function testGetTargets()
    {
        $constraint = new CountryShippingServicesConstraint();

        static::assertSame(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
