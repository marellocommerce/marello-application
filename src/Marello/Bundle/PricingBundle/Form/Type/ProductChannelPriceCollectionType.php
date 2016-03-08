<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductChannelPriceCollectionType extends AbstractType
{
    const NAME = 'marello_product_channel_price_collection';

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'type'                 => ProductChannelPriceType::NAME,
                'show_form_when_empty' => false,
                'error_bubbling'       => false,
                'cascade_validation'   => true,
                'prototype_name'       => '__nameproductchannelprice__',
                'prototype'            => true,
                'handle_primary'       => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
