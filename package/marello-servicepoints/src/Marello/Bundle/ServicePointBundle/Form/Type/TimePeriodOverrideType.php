<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\TimePeriodOverride;

class TimePeriodOverrideType extends AbstractTimePeriodType
{
    protected function getDataClass()
    {
        return TimePeriodOverride::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        $x = parent::getBlockPrefix();
        var_dump($x);
        return $x;
    }

}
