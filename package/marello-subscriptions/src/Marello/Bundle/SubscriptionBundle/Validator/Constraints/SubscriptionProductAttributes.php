<?php

namespace Marello\Bundle\SubscriptionBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class SubscriptionProductAttributes extends Constraint
{
    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'marello_subscription.subscription_product_attributes_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
