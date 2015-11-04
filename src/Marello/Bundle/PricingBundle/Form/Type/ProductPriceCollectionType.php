<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductPriceCollectionType extends AbstractType
{
    const NAME = 'marello_product_price_collection';

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
                'type'                 => ProductPriceType::NAME,
                'show_form_when_empty' => true,
                'error_bubbling'       => false,
                'cascade_validation'   => true,
                'prototype_name'       => '__nameproductprice__',
                'prototype'            => true,
                'handle_primary'       => false,
                'attr'                 => [
                    'class' => 'table table-condensed table-striped table-bordered',
                ],
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
