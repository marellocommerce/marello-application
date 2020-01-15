<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\TimePeriodOverride;

class TimePeriodOverrideType extends AbstractTimePeriodType
{
    protected function getDataClass()
    {
        return TimePeriodOverride::class;
    }
}
