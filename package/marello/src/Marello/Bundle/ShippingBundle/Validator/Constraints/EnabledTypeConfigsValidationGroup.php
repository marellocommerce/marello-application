<?php

namespace Marello\Bundle\ShippingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class EnabledTypeConfigsValidationGroup extends Constraint
{
    /**
     * @var string
     */
    public $message = 'marello.shipping.shippingrule.type.config.count.message';

    /**
     * @var int
     */
    public $min = 1;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'marello_shipping_enabled_type_config_validation_group_validator';
    }
}
