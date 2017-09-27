<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\WarehouseAddedToUserGroupValidator;
use Symfony\Component\Validator\Constraint;

class WarehouseAddedToUserGroup extends Constraint
{
    public $message = 'marelloenterprise.inventory.validation.messages.error.warehouse_added_to_user_group';

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
        return WarehouseAddedToUserGroupValidator::ALIAS;
    }
}
