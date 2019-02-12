<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ProductSupplierRelationCollectionType extends AbstractType
{
    const BLOCK_PREFIX = 'marello_product_supplier_relation_collection_form';

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'           => ProductSupplierRelationType::class,
            'show_form_when_empty' => false,
            'error_bubbling'       => true,
            'constraints'          => [new Valid()],
            'prototype_name'       => '__nameproductsupplierrelation__',
            'prototype'            => true,
            'handle_primary'       => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
