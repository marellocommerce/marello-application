<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseOrderItemReceiveCollectionType
{
    const NAME = 'marello_purchase_order_item_receive_collection';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type' => PurchaseOrderItemReceiveType::NAME,
            'show_form_when_empty' => false,
            'error_bubbling' => false,
            'cascade_validation' => true,
            'prototype_name' => '__namepurchaseorderitemrecieve__',
            'prototype' => true,
            'handle_primary' => false,
            'allow_add' => false,
            'allow_detele' => false,
        ]);
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

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::NAME;
    }
}
