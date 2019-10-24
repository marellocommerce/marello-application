<?php

namespace Marello\Bundle\ServicePointBundle\Constraint;

use Marello\Bundle\ServicePointBundle\Provider\DayOfWeekProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DayOfWeekValidator extends ConstraintValidator
{
    protected $dayOfWeekProvider;

    public function __construct(DayOfWeekProvider $dayOfWeekProvider)
    {
        $this->dayOfWeekProvider = $dayOfWeekProvider;
    }

    public function validate($value, Constraint $constraint)
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!is_scalar($value)) {
            throw new UnexpectedTypeException($value, 'scalar');
        }

        $choices = array_values($this->dayOfWeekProvider->getDaysOfWeekChoices());
        if (!in_array($value, $choices)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
