<?php

namespace Marello\Bundle\PurchaseOrderBundle\Validator\Constraints;

use Marello\Bundle\PurchaseOrderBundle\Validator\PurchaseOrderItemValidator;
use Symfony\Component\Validator\Constraint;

class PurchaseOrderItemConstraint extends Constraint
{
    /** @var string */
    public $productMessage = 'Product can not be null.';
    public $orderedAmountMessage = 'Ordered Amount must be higher than 0.';
    public $purchasePriceMessage = 'Purchase Price must be higher than 0.';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return PurchaseOrderItemValidator::class;
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
