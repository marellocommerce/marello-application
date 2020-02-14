<?php

namespace Marello\Bundle\ServicePointBundle\Model;

use Marello\Bundle\ServicePointBundle\Entity\AbstractTimePeriod;

class ExtendTimePeriodOverride extends AbstractTimePeriod
{
    public function __construct()
    {
        parent::__construct();
    }
}
