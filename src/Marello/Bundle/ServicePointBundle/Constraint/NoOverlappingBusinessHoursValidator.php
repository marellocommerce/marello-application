<?php

namespace Marello\Bundle\ServicePointBundle\Constraint;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\ServicePointBundle\Entity\AbstractTimePeriod;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoOverlappingBusinessHoursValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        if (!$value instanceof Collection) {
            throw new UnexpectedTypeException($value, Collection::class);
        }

        foreach ($value as $i => $item) {
            $sameDayHours = $value->filter(function (AbstractTimePeriod $x) use ($item) {
                return $item !== $x && $item->getDayOfWeek() === $x->getDayOfWeek();
            });
            foreach ($sameDayHours as $sameDayItem) {
                if ($this->isHoursOverlap($sameDayItem, $item)) {
                    $this->context
                        ->buildViolation($constraint->message)
                        ->atPath('['.$i.'].openTime')
                        ->addViolation()
                    ;
                }
            }
        }
    }

    protected function isHoursOverlap(AbstractTimePeriod $first, AbstractTimePeriod $second)
    {
        if ($first->getOpenTime() <= $second->getOpenTime()) {
            if ($first->getCloseTime() >= $second->getOpenTime()) {
                return true;
            }
        } elseif ($second->getCloseTime() >= $first->getOpenTime()) {
            return true;
        }

        return false;
    }
}
