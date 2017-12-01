<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSupplierRelationCollectionType extends AbstractType
{
    const NAME = 'marello_product_supplier_relation_collection_form';

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type'                 => ProductSupplierRelationType::NAME,
            'show_form_when_empty' => false,
            'error_bubbling'       => false,
            'cascade_validation'   => true,
            'prototype_name'       => '__nameproductsupplierrelation__',
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
    public function getBlockPrefix()
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
