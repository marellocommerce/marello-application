<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\TimePeriodOverride;

class TimePeriodOverrideType extends AbstractTimePeriodType
{
    const BLOCK_PREFIX = 'marello_servicepoint_business_hours_override_time_period_override';

    protected function getDataClass()
    {
        return TimePeriodOverride::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
