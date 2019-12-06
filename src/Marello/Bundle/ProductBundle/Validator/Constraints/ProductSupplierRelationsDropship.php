<?php

namespace Marello\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ProductSupplierRelationsDropship extends Constraint
{
    /** @var string */
    public $message = 'marello.product.messages.error.suppliers.dropship';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'marello_product.product_supplier_relations_dropship_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
