<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssembledPriceListCollectionType extends AbstractType
{
    const NAME = 'marello_assembled_price_list_collection';

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
                'type'                 => AssembledPriceListType::NAME,
                'show_form_when_empty' => false,
                'error_bubbling'       => false,
                'cascade_validation'   => true,
                'prototype_name'       => '__nameproductprice__',
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
