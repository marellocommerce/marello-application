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
namespace Marello\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class GreaterThanOrEqualToValue extends Constraint
{
    /** @var string */
    public $message = 'Field %s needs to be greater than or equal to field %s';
    public $fields = array();
    public $errorPath = null;


    public function getRequiredOptions()
    {
        return array('fields');
    }

    public function getDefaultOption()
    {
        return 'fields';
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'marello_core.greater_than_or_equal_to_value_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
