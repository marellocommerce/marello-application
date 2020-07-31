<?php

namespace Marello\Bundle\Magento2Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ValidWebsiteToSalesChannelMapItems extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::PROPERTY_CONSTRAINT];
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'marello_magento2.valid_website_to_sales_channel_map_items';
    }
}
