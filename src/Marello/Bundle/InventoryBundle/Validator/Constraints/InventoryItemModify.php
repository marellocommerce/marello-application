<?php

namespace Marello\Bundle\InventoryBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InventoryItemModify extends Constraint
{
    /** @var string */
    public $message = 'marello.inventory.validation.inventory_item_modify.quantity';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
