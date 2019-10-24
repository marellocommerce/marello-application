<?php

namespace Marello\Bundle\ServicePointBundle\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoDuplicateBusinessHoursValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        if (!is_iterable($value)) {
            throw new UnexpectedTypeException($value, 'iterable');
        }

        $usedDays = [];
        foreach ($value as $i => $item) {
            if (isset($usedDays[$item->getDayOfWeek()])) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->atPath(sprintf('[%d]', $i))
                    ->addViolation()
                ;
            } else {
                $usedDays[$item->getDayOfWeek()] = true;
            }
        }
    }
}
