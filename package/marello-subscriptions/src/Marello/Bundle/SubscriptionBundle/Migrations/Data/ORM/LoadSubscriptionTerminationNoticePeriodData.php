<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

class LoadSubscriptionTerminationNoticePeriodData extends AbstractLoadSubscriptionEnumData
{
    const ENUM_CLASS = 'marello_substernotper';
    
    const DURATION_1_MONTH = '01';
    const DURATION_3_MONTHS = '03';
    const DURATION_6_MONTHS = '06';
    const DURATION_12_MONTHS = '12';
    const DURATION_24_MONTHS = '24';

    /** @var array */
    protected $data = [
        self::DURATION_1_MONTH => '1 month',
        self::DURATION_3_MONTHS => '3 months',
        self::DURATION_6_MONTHS => '6 months',
        self::DURATION_12_MONTHS => '12 months',
        self::DURATION_24_MONTHS => '24 months'
    ];
}
