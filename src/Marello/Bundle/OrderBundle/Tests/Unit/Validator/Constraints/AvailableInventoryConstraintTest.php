<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Validator\Constraints;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Validator\Constraint;

use Marello\Bundle\OrderBundle\Validator\Constraints\AvailableInventoryConstraint;

class AvailableInventoryContraintTest extends TestCase
{
    /** @var AvailableInventoryConstraint $constraint */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->constraint = new AvailableInventoryConstraint(['fields' => ['somefield1', 'somefield2']]);
    }

    /**
     * Test configuration of constraint
     */
    public function testConfiguration()
    {
        $this->assertEquals(['fields'], $this->constraint->getRequiredOptions());
        $this->assertEquals('fields', $this->constraint->getDefaultOption());
        $this->assertEquals('marello_order.available_inventory_validator', $this->constraint->validatedBy());
        $this->assertEquals(Constraint::CLASS_CONSTRAINT, $this->constraint->getTargets());
    }
}
