<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

use Marello\Bundle\OrderBundle\Validator\Constraints\AvailableInventory;

class AvailableInventoryContraintTest extends \PHPUnit_Framework_TestCase
{
    /** @var AvailableInventory $constraint */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->constraint = new AvailableInventory(['fields' => ['somefield1', 'somefield2']]);
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
