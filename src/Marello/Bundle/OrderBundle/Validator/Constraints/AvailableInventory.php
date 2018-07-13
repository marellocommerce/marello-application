<?php
/**
 * This application uses Open Source components. You can find the source code
 * of their open source projects along with license information below. We acknowledge
 * and are grateful to these developers for their contributions to open source.
 *
 * This class is inspired by Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity
 * all efforts and inspiration which have paved the road for this class belong to Fabien Potencier.
 *
 * Project: Symfony (https://symfony.com)
 * Copyright (c) 2004-2015 Fabien Potencier. All right reserved.
 */
namespace Marello\Bundle\OrderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class AvailableInventory extends Constraint
{
    /** @var string */
    public $message = 'marello.order.item.available_inventory';
    public $fields = array();
    public $errorPath = null;

    /**
     * @return array
     */
    public function getRequiredOptions()
    {
        return array('fields');
    }

    /**
     * @return string
     */
    public function getDefaultOption()
    {
        return 'fields';
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'marello_order.available_inventory_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
