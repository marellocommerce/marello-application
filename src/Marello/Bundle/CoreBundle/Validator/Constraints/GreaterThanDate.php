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

class GreaterThanDate extends Constraint
{
    /** @var string */
    public $message = 'Date needs to be greater';
    public $field = '';
    public $greaterThan = 'today';
    public $nullable = false;
    public $errorPath = null;


    public function getRequiredOptions()
    {
        return array('field');
    }

    public function getDefaultOption()
    {
        return 'field';
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'greater_than_date';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
