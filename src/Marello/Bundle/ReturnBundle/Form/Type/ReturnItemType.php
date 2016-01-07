<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class ReturnItemType extends AbstractType
{
    const NAME = 'marello_return_item';

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }
}
