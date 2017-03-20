<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductChannelTaxRelationCollectionType extends AbstractType
{
    const NAME = 'marello_product_channel_tax_relation_collection_form';

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type'                 => ProductChannelTaxRelationType::NAME,
            'show_form_when_empty' => false,
            'error_bubbling'       => false,
            'cascade_validation'   => true,
            'prototype_name'       => '__nameproductchanneltaxrelation__',
            'prototype'            => true,
            'handle_primary'       => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::NAME;
    }
}
