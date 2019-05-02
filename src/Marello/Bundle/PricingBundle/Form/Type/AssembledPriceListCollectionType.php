<?php

namespace Marello\Bundle\PricingBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class AssembledPriceListCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_assembled_price_list_collection';

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
                'entry_type'           => AssembledPriceListType::class,
                'show_form_when_empty' => false,
                'error_bubbling'       => false,
                'constraints'          => [new Valid()],
                'prototype_name'       => '__nameproductprice__',
                'prototype'            => true,
                'handle_primary'       => false,
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
