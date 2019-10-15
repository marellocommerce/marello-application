<?php

namespace Marello\Bundle\InventoryBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class InventoryOrderOnDemand extends Constraint
{
    /** @var string */
    public $message = 'marello.inventory.validation.messages.error.inventoryitem.order_on_demand_allowed_no_supplier';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'marello_inventory.order_on_demand_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
