<?php

namespace Marello\Bundle\ServicePointBundle\Constraint;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TimeIntervalValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }
        if (!is_object($value) && !is_array($value)) {
            throw new UnexpectedTypeException($value, 'object or array');
        }

        $startValue = $this->getDatetimeValue($value, $constraint->startField);
        $endValue = $this->getDatetimeValue($value, $constraint->endField);

        if ($startValue !== null && $endValue !== null && $startValue >= $endValue) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath($constraint->endField)
                ->addViolation()
            ;
        }
    }

    protected function getDatetimeValue($object, $field)
    {
        $value = self::accessor()->getValue($object, $field);
        if ($value === null) {
            return null;
        }

        if (!$value instanceof \DateTime) {
            throw new UnexpectedTypeException($value, \DateTime::class);
        }

        $normalizedValue = new \DateTime();
        $normalizedValue->setTime($value->format('H'), $value->format('i'), $value->format('s'));

        return $normalizedValue;
    }

    protected function accessor()
    {
        static $accessor = null;
        if ($accessor === null) {
            $accessor = PropertyAccess::createPropertyAccessor();
        }

        return $accessor;
    }
}
