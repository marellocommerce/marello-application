<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\WarehouseAddedToLinkedGroupValidator;
use Symfony\Component\Validator\Constraint;

class WarehouseAddedToLinkedGroup extends Constraint
{
    public $message = 'marelloenterprise.inventory.validation.messages.error.warehouse_added_to_linked_group';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return WarehouseAddedToLinkedGroupValidator::ALIAS;
    }
}
