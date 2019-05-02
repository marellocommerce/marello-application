<?php

namespace Marello\Bundle\ShippingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ShippingRuleEnable extends Constraint
{
    /**
     * @var string
     */
    public $message = 'marello.shipping.shippingrule.enabled.message';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
