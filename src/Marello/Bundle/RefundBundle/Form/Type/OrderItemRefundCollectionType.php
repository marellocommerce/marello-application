<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemRefundCollectionType extends AbstractType
{
    const NAME = 'marello_order_item_refund_collection';

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type' => OrderItemRefundType::NAME,
        ]);
    }

    public function getParent()
    {
        return 'collection';
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
}
