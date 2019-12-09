<?php


namespace Marello\Bundle\ServicePointBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class TimeInterval extends Constraint
{
    public $startField;
    public $endField;
    public $message = 'marello.servicepoint.time_interval.message';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
