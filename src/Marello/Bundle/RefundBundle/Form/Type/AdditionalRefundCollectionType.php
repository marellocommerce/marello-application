<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdditionalRefundCollectionType extends AbstractType
{
    const NAME = 'marello_additional_refund_collection';

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'type'         => AdditionalRefundType::NAME,
                'allow_add'    => true,
                'allow_remove' => true,
                'prototype_name'       => '__namerefunditem__',
                'prototype'            => true,
                'handle_primary'       => false,
                'show_form_when_empty' => false,
                'error_bubbling'       => false,
            ]
        );
    }

    public function getParent()
    {
        return CollectionType::NAME;
    }

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
