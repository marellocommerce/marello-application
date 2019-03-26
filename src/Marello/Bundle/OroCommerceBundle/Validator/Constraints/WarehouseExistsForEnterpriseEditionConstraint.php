<?php

namespace Marello\Bundle\OroCommerceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class WarehouseExistsForEnterpriseEditionConstraint extends Constraint
{
    /** @var string */
    public $message = 'marello.orocommerce.orocommercesettings.messages.error.no_warehouse_for_enterprise_edition';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'marello_orocommerce.warehouse_exists_for_enterprise_edition_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
