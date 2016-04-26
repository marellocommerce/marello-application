<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReturnItemCollectionType extends AbstractType
{
    const NAME = 'marello_return_item_collection';

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type'                 => ReturnItemType::NAME,
            'options'              => function (Options $options) {
                return ['update' => $options['update']];
            },
            'show_form_when_empty' => false,
            'error_bubbling'       => false,
            'cascade_validation'   => true,
            'prototype_name'       => '__namereturnitem__',
            'prototype'            => true,
            'handle_primary'       => false,
            'by_reference'         => false,
            'update'               => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::NAME;
    }
}
