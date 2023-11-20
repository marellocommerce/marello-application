<?php

namespace Marello\Bundle\PaymentBundle\Validator;

use Marello\Bundle\PaymentBundle\Entity\Payment;
use Marello\Bundle\PaymentBundle\Validator\Constraints\TotalPaid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TotalPaidValidator extends ConstraintValidator
{
    /**
     * Checks if the passed entity is unique in collection.
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof TotalPaid) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\TotalPaid');
        }
        if ($entity instanceof Payment) {
            $paymentSource = $entity->getPaymentSource();
            if ($paymentSource) {
                $totalPaid = $entity->getTotalPaid();
                $totalDue = $paymentSource->getTotalDue();
                if ($totalPaid > $totalDue) {
                    $this->context->buildViolation($constraint->message)
                        ->atPath('totalPaid')
                        ->addViolation();
                }
            }
        }
    }
}
