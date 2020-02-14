<?php

namespace Marello\Bundle\ServicePointBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class NoOverlappingBusinessHours extends Constraint
{
    public $message = 'marello.servicepoint.no_overlapping_business_hours.message';
}
