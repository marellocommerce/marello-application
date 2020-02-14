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

        $itemCount = count($value);
        for ($i = 0; $i < $itemCount; $i++) {
            $item = $value[$i];
            for ($j = $i + 1; $j < $itemCount; $j++) {
                $compareItem = $value[$j];
                if ($this->isHoursOverlap($compareItem, $item)) {
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
        if ($first === $second) {
            return false;
        }

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
