<?php

namespace Marello\Bundle\ReturnBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ReturnItemCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_return_item_collection';

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'           => ReturnItemType::class,
            'options'              => function (Options $options) {
                return ['update' => $options['update']];
            },
            'show_form_when_empty' => false,
            'error_bubbling'       => false,
            'constraints'          => [new Valid()],
            'prototype_name'       => '__namereturnitem__',
            'prototype'            => true,
            'handle_primary'       => false,
            'by_reference'         => false,
            'update'               => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
