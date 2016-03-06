<?php

namespace Marello\Bundle\ProductBundle\Form\Type;

use Marello\Bundle\InventoryBundle\Form\Type\InventoryItemApiType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductApiType extends AbstractType
{
    const NAME = 'marello_product_api_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('sku')
            ->add('status', 'entity', [
                'class' => 'Marello\Bundle\ProductBundle\Entity\ProductStatus',
            ])
            ->add('prices')
            ->add('channels')
            ->add('inventory', 'collection', [
                'property_path' => 'inventoryItems',
                'type'          => new InventoryItemApiType(),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'Marello\Bundle\ProductBundle\Entity\Product',
                'cascade_validation' => true,
                'csrf_protection'    => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
