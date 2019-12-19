<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\TimePeriod;

class TimePeriodType extends AbstractTimePeriodType
{
    protected function getDataClass()
    {
        return TimePeriod::class;
    }
}
