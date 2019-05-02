<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemRefundCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_order_item_refund_collection';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => OrderItemRefundType::class,
            'allow_add' => false,
            'allow_delete' => false,
        ]);
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
