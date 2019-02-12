<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class PurchaseOrderItemCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_purchase_order_item_collection';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'           => PurchaseOrderItemType::class,
            'show_form_when_empty' => false,
            'error_bubbling'       => false,
            'constraints'          => [new Valid()],
            'prototype_name'       => '__namepurchaseorderitem__',
            'prototype'            => true,
            'handle_primary'       => false,
            'allow_add'            => true,
            'allow_delete'         => true
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
