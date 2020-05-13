<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ReplenishmentOrderItemCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_replenishment_order_item_collection';

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => ReplenishmentOrderItemType::class,
            'show_form_when_empty' => false,
            'error_bubbling' => false,
            'constraints' => [new Valid()],
            'prototype_name' => '__namereplenishmentorderitem__',
            'prototype' => true,
            'handle_primary' => false,
        ]);
    }

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
    public function getParent()
    {
        return CollectionType::class;
    }
}
