<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseOrderItemCollectionType extends AbstractType
{
    const NAME = 'marello_purchase_order_item_collection';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type'                 => PurchaseOrderItemType::NAME,
            'show_form_when_empty' => false,
            'error_bubbling'       => false,
            'cascade_validation'   => true,
            'prototype_name'       => '__namepurchaseorderitem__',
            'prototype'            => true,
            'handle_primary'       => false,
            'allow_add'            => true,
            'allow_delete'         => true
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
