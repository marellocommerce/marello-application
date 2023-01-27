<?php

namespace Marello\Bundle\PurchaseOrderBundle\Form\Type;

use Marello\Bundle\PurchaseOrderBundle\Entity\PurchaseOrderItem;
use Oro\Bundle\FormBundle\Form\Type\MultipleEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseOrderAdvisedItemCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_purchase_order_advised_item_collection';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'add_acl_resource'      => 'marello_purchase_order_view',
            'class'                 => PurchaseOrderItem::class,
            'default_element'       => 'default_purchase_order_item',
            'required'              => false,
            'selector_window_title' => 'marello.product.entity_label',
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
        return MultipleEntityType::class;
    }
}
