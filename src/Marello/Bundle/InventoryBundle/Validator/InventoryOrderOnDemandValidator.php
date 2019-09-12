<?php

namespace Marello\Bundle\InventoryBundle\Validator;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\OrderBundle\Event\ProductAvailableInventoryValidationEvent;
use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

class InventoryOrderOnDemandValidator extends ConstraintValidator
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        DoctrineHelper $doctrineHelper
    ) {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Checks if the passed entity is unique in collection.
     * @param mixed $entity
     * @param Constraint $constraint
     * @throws UnexpectedTypeException
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof InventoryItem) {
            return;
        }
        $product = $entity->getProduct();
        if ($entity->isOrderOnDemandAllowed() && !$product->getPreferredSupplier()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('orderOnDemandAllowed')
                ->addViolation();
        }
    }
}
