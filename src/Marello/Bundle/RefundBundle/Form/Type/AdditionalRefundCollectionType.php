<?php

namespace Marello\Bundle\RefundBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdditionalRefundCollectionType extends AbstractType
{
    const NAME = 'marello_additional_refund_collection';

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'type'         => AdditionalRefundType::NAME,
                'allow_add'    => false,
                'allow_remove' => false,
            ]
        );
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
