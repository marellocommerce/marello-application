<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProductCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_collection';

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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'entry_type'           => ProductType::class,
                'show_form_when_empty' => true,
                'error_bubbling'       => false,
                'constraints'          => [new Valid()],
                'prototype_name'       => '__nameproducts__',
                'prototype'            => true,
                'handle_primary'       => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
