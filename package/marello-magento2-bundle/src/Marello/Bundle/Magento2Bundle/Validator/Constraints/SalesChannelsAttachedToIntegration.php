<?php

namespace Marello\Bundle\Magento2Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class SalesChannelsAttachedToIntegration extends Constraint
{
    /** @var string */
    public $message = <<<MESSAGE
You tried to unlink sales channels with ids "{{ forbidden_to_remove_sales_channel_ids }}", but this is forbidden, 
because they used by Magento integration.'
MESSAGE;

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
        return 'marello_magento2.sales_channel_attached_to_integration_validator';
    }
}
