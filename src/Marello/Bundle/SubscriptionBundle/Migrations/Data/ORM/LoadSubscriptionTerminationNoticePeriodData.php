<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

class LoadSubscriptionTerminationNoticePeriodData extends AbstractLoadSubscriptionEnumData
{
    const ENUM_CLASS = 'marello_substernotper';
    
    const DURATION_1_MONTH = '01';
    const DURATION_3_MONTHS = '03';
    const DURATION_6_MONTHs = '06';
    const DURATION_12_MONTHS = '12';
    const DURATION_24_MONTHS = '24';

    /** @var array */
    protected $data = [
        '01' => '1 month',
        '03' => '3 months',
        '06' => '6 months',
        '12' => '12 months',
        '24' => '24 months'
    ];
}
