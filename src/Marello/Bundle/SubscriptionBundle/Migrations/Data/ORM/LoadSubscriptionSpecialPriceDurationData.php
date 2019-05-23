<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

class LoadSubscriptionSpecialPriceDurationData extends AbstractLoadSubscriptionEnumData
{
    const ENUM_CLASS = 'marello_subsspprdur';
    
    const CONTINUOUSLY = 'continuously';
    const EQUAL_TO_SUBSCRIPTION_DURATION = 'equal_to_subscription_duration';

    /** @var array */
    protected $data = [
        self::CONTINUOUSLY => 'continuously',
        self::EQUAL_TO_SUBSCRIPTION_DURATION => 'equal to subscription duration'
    ];
}
