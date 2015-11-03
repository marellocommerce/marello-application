<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DefaultWarehouseExists extends Constraint
{
    public $message = 'There must be a default warehouse.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'marello_enterprise_inventory.default_warehouse_exists';
    }
}
