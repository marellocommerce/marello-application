<?php

namespace Marello\Bundle\SubscriptionBundle\Validator;

use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Provider\AvailableInventoryProvider;
use Marello\Bundle\OrderBundle\Validator\Constraints\AvailableInventory;

class SubscriptionProductAttributesValidator extends ConstraintValidator
{
    /**
     * Checks if the passed entity is unique in collection.
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($entity, Constraint $constraint)
    {
        /** @var Product $entity */
        $type = $entity->getType();
        if ($type === 'subscription') {
            if (!$entity->getSubscriptionDuration()) {
                $this->context->buildViolation('Subscription Duration can\'t be empty')
                    ->atPath('subscriptionDuration')
                    ->addViolation();
            }
            if (!$entity->getPaymentTerm()) {
                $this->context->buildViolation('Payment Term can\'t be empty')
                    ->atPath('paymentTerm')
                    ->addViolation();
            }
        }
    }
}
