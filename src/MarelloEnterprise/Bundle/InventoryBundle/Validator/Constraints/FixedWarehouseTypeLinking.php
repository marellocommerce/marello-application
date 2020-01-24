<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\FixedWarehouseTypeLinkingValidator;
use Symfony\Component\Validator\Constraint;

class FixedWarehouseTypeLinking extends Constraint
{
    public $message = 'marelloenterprise.inventory.validation.messages.error.fixed_warehouse_type_linking';

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
        return FixedWarehouseTypeLinkingValidator::ALIAS;
    }
}
