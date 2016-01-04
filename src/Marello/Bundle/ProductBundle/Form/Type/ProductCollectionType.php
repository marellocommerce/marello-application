<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;

class ProductCollectionType extends AbstractType
{
    const NAME = 'marello_product_collection';

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
                'type'                 => ProductType::NAME,
                'show_form_when_empty' => true,
                'error_bubbling'       => false,
                'cascade_validation'   => true,
                'prototype_name'       => '__nameproducts__',
                'prototype'            => true,
                'handle_primary'       => false
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
