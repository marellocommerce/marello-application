<?php

namespace Marello\Bundle\ServicePointBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class NoDuplicateBusinessHours extends Constraint
{
    public $message = 'marello.servicepoint.no_duplicate_business_hours.message';
}
