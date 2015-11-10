<?php

namespace Marello\Bundle\InventoryBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class WarehouseInventory extends Constraint
{
    /** @var string */
    public $message = 'marello.inventory.validation.warehouse_inventory';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
