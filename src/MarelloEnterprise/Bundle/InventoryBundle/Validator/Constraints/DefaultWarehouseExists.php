<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Validator\Constraints;

use MarelloEnterprise\Bundle\InventoryBundle\Validator\DefaultWarehouseExistsValidator;
use Symfony\Component\Validator\Constraint;

class DefaultWarehouseExists extends Constraint
{
    public $message = 'marelloenterprise.inventory.validation.messages.error.default_warehouse_exists';

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
        return DefaultWarehouseExistsValidator::ALIAS;
    }
}
