<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdditionalRefundCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_additional_refund_collection';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'entry_type'           => AdditionalRefundType::class,
                'allow_add'            => true,
                'allow_remove'         => true,
                'prototype_name'       => '__namerefunditem__',
                'prototype'            => true,
                'handle_primary'       => false,
                'show_form_when_empty' => false,
                'error_bubbling'       => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
