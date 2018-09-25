<?php

namespace Marello\Bundle\CoreBundle\Tests\Unit\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

use Marello\Bundle\CoreBundle\Validator\Constraints\GreaterThanOrEqualToValue;

class GreaterThanOrEqualToValueTest extends \PHPUnit_Framework_TestCase
{
    /** @var GreaterThanOrEqualToValue $constraint */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->constraint = new GreaterThanOrEqualToValue(['fields' => ['somefield1', 'somefield2']]);
    }

    /**
     * Test configuration of constraint
     */
    public function testConfiguration()
    {
        $this->assertEquals(['fields'], $this->constraint->getRequiredOptions());
        $this->assertEquals('fields', $this->constraint->getDefaultOption());
        $this->assertEquals('marello_core.greater_than_or_equal_to_value_validator', $this->constraint->validatedBy());
        $this->assertEquals(Constraint::CLASS_CONSTRAINT, $this->constraint->getTargets());
    }
}
