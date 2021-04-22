<?php

namespace Marello\Bundle\PaymentBundle\Validator;

use Marello\Bundle\PaymentBundle\Entity\Payment;
use Marello\Bundle\PaymentBundle\Migrations\Data\ORM\LoadPaymentStatusData;
use Marello\Bundle\PaymentBundle\Validator\Constraints\PaymentStatus;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentStatusValidator extends ConstraintValidator
{
    /**
     * Checks if the passed entity is unique in collection.
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof PaymentStatus) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\PaymentStatus');
        }
        if ($entity instanceof Payment) {
            $paymentSource = $entity->getPaymentSource();
            $status = $entity->getStatus()->getId();
            if ((!$paymentSource && $status === LoadPaymentStatusData::ASSIGNED) ||
                ($paymentSource && $status === LoadPaymentStatusData::UNASSIGNED)
            ) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('status')
                    ->addViolation();
            }
        }
    }
}
