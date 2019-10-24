<?php

namespace Marello\Bundle\ServicePointBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class DayOfWeek extends Constraint
{
    public $message = 'marello.servicepoint.day_of_week.invalid.message';
}
