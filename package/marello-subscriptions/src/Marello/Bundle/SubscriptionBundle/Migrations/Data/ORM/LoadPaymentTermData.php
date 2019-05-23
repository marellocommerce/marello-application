<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

class LoadPaymentTermData extends AbstractLoadSubscriptionEnumData
{
    const ENUM_CLASS = 'marello_subspaymterm';
    
    const DURATION_1_MONTH = '01';
    const DURATION_3_MONTHS = '03';
    const DURATION_6_MONTHS = '06';
    const DURATION_12_MONTHS = '12';

    /** @var array */
    protected $data = [
        '01' => '1 month',
        '03' => '3 months',
        '06' => '6 months',
        '12' => '12 months'
    ];
}
