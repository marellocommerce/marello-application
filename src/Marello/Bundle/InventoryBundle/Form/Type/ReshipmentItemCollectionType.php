<?php

namespace Marello\Bundle\InventoryBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ReshipmentItemCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_inventory_reshipment_item_collection';

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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'allow_add' => false,
                'allow_delete' => false,
                'entry_type' => ReshipmentItemType::class,
                'show_form_when_empty' => false,
                'error_bubbling' => true,
                'constraints' => [new Valid()],
                'prototype' => false,
                'handle_primary' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
